<?php

namespace App\Services;

use App\Enums\QueueServiceType;
use App\Enums\QueueTicketStatus;
use App\Models\QueueTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function issue(
        int $organizationId,
        string $serviceType,
        ?User $user = null,
        ?string $nim = null,
        ?int $bookingId = null,
    ): QueueTicket {
        $type = QueueServiceType::tryFrom($serviceType) ?? QueueServiceType::Consultation;
        $prefix = $this->prefix($type);
        $date = now()->toDateString();

        return DB::transaction(function () use ($organizationId, $type, $prefix, $date, $user, $nim, $bookingId): QueueTicket {
            $last = QueueTicket::query()
                ->where('organization_id', $organizationId)
                ->whereDate('service_date', $date)
                ->where('number', 'like', $prefix.'-%')
                ->lockForUpdate()
                ->orderByDesc('number')
                ->value('number');

            $sequence = $last === null ? 1 : ((int) substr((string) $last, 2)) + 1;

            $ticket = QueueTicket::create([
                'organization_id' => $organizationId,
                'number' => sprintf('%s-%03d', $prefix, $sequence),
                'service_type' => $type,
                'status' => QueueTicketStatus::Waiting,
                'counter' => $prefix,
                'user_id' => $user?->id,
                'booking_id' => $bookingId,
                'nim' => $nim ?? $user?->nim,
                'issued_at' => now(),
                'service_date' => $date,
            ]);

            $this->logger->log('queue.issued', $ticket, "Tiket antrean {$ticket->number} diterbitkan.", $user, [
                'service_type' => $type->value,
            ]);

            return $ticket;
        });
    }

    public function callNext(int $organizationId, ?string $counter, User $actor): ?QueueTicket
    {
        $ticket = QueueTicket::query()
            ->where('organization_id', $organizationId)
            ->whereDate('service_date', now()->toDateString())
            ->where('status', QueueTicketStatus::Waiting->value)
            ->when($counter, fn ($query, string $value) => $query->where('counter', $value))
            ->orderBy('issued_at')
            ->first();

        if ($ticket === null) {
            return null;
        }

        $ticket->update([
            'status' => QueueTicketStatus::Called,
            'counter' => $counter ?? $ticket->counter,
            'called_at' => now(),
        ]);

        $this->logger->log('queue.called', $ticket, "Antrean {$ticket->number} dipanggil.", $actor);

        return $ticket->fresh();
    }

    public function serve(QueueTicket $ticket, User $actor): QueueTicket
    {
        $ticket->update([
            'status' => QueueTicketStatus::Done,
            'served_at' => now(),
        ]);

        $this->logger->log('queue.served', $ticket, "Antrean {$ticket->number} selesai dilayani.", $actor);

        return $ticket->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function board(int $organizationId): array
    {
        $today = QueueTicket::query()
            ->where('organization_id', $organizationId)
            ->whereDate('service_date', now()->toDateString());

        $waiting = (clone $today)
            ->where('status', QueueTicketStatus::Waiting->value)
            ->orderBy('issued_at')
            ->get();

        $counters = (clone $today)
            ->whereIn('status', [QueueTicketStatus::Called->value, QueueTicketStatus::Serving->value])
            ->orderByDesc('called_at')
            ->get()
            ->unique('counter')
            ->keyBy('counter');

        return [
            'waiting' => $waiting,
            'waiting_count' => $waiting->count(),
            'counters' => $counters,
            'done_count' => (clone $today)->where('status', QueueTicketStatus::Done->value)->count(),
        ];
    }

    public function prune(int $days = 7): int
    {
        return QueueTicket::query()
            ->whereDate('service_date', '<', now()->subDays($days)->toDateString())
            ->whereIn('status', [QueueTicketStatus::Done->value, QueueTicketStatus::Skipped->value])
            ->delete();
    }

    private function prefix(QueueServiceType $type): string
    {
        return match ($type) {
            QueueServiceType::Pickup => 'A',
            QueueServiceType::Return => 'B',
            QueueServiceType::Consultation => 'T',
        };
    }
}
