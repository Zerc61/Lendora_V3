<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Collection;

class OperatorDashboardService
{
    public function __construct(
        private readonly ShiftService $shifts,
        private readonly BookingService $bookings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $organizationId = $user->organization_id;
        $today = now()->toDateString();

        $kpis = [
            'pending' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Pending->value)
                ->count(),
            'pickup_today' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Approved->value)
                ->whereDate('requested_start_date', $today)
                ->count(),
            'due_today' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Active->value)
                ->whereDate('due_at', $today)
                ->count(),
            'late' => Booking::query()
                ->where('organization_id', $organizationId)
                ->where('status', BookingStatus::Active->value)
                ->whereDate('due_at', '<', $today)
                ->count(),
        ];

        return [
            'kpis' => $kpis,
            'todayQueue' => $this->todayQueue($organizationId, $today, $user),
            'handoverSchedule' => $this->handoverSchedule($organizationId, $today),
            'shelfSummary' => $this->shelfSummary($organizationId),
            'shift' => $this->shifts->current($organizationId),
            'lastShiftNote' => $this->shifts->lastNote($organizationId),
        ];
    }

    /**
     * @return Collection<int, array{booking: Booking, tab: string, label: string, block_reason: string|null}>
     */
    private function todayQueue(int $organizationId, string $today, User $user): Collection
    {
        $pending = Booking::query()
            ->with(['borrower', 'items.asset'])
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Pending->value)
            ->orderBy('requested_start_date')
            ->limit(6)
            ->get()
            ->each(fn (Booking $booking) => $booking->setAttribute('queue_tab', 'approval'));

        $pickups = Booking::query()
            ->with(['borrower', 'items.asset'])
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Approved->value)
            ->whereDate('requested_start_date', '<=', $today)
            ->orderBy('requested_start_date')
            ->limit(6)
            ->get()
            ->each(fn (Booking $booking) => $booking->setAttribute('queue_tab', 'pickup'));

        $returns = Booking::query()
            ->with(['borrower', 'items.asset'])
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Active->value)
            ->whereDate('due_at', '<=', $today)
            ->orderBy('due_at')
            ->limit(6)
            ->get()
            ->each(fn (Booking $booking) => $booking->setAttribute('queue_tab', 'return'));

        return $pending
            ->concat($pickups)
            ->concat($returns)
            ->map(fn (Booking $booking): array => [
                'booking' => $booking,
                'tab' => (string) $booking->getAttribute('queue_tab'),
                'label' => match ($booking->getAttribute('queue_tab')) {
                    'approval' => __('ui.dashboard.operator.tab_approval'),
                    'pickup' => __('ui.dashboard.operator.tab_pickup'),
                    default => __('ui.dashboard.operator.tab_return'),
                },
                'block_reason' => $booking->getAttribute('queue_tab') === 'approval'
                    ? $this->bookings->decisionBlockReason($user, $booking)
                    : null,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{time: string, activity: string, booking: Booking}>
     */
    private function handoverSchedule(int $organizationId, string $today): Collection
    {
        $settings = app(SettingService::class);
        $pickupTime = (string) $settings->get('pickup_open_time', '08:30', $organizationId);
        $returnTime = (string) $settings->get('return_close_time', '16:00', $organizationId);

        $pickups = Booking::query()
            ->with('borrower')
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Approved->value)
            ->whereDate('requested_start_date', $today)
            ->get()
            ->map(fn (Booking $booking): array => [
                'time' => $pickupTime,
                'activity' => __('ui.dashboard.operator.activity_pickup'),
                'booking' => $booking,
            ]);

        $returns = Booking::query()
            ->with('borrower')
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Active->value)
            ->whereDate('due_at', $today)
            ->get()
            ->map(fn (Booking $booking): array => [
                'time' => $returnTime,
                'activity' => __('ui.dashboard.operator.activity_return'),
                'booking' => $booking,
            ]);

        return $pickups->concat($returns)->sortBy('time')->values();
    }

    /**
     * @return Collection<int, array{location: string, total: int, available: int, borrowed: int, maintenance: int}>
     */
    private function shelfSummary(int $organizationId): Collection
    {
        $rows = Asset::query()
            ->where('organization_id', $organizationId)
            ->selectRaw("coalesce(location, '-') as shelf, status, count(*) as aggregate")
            ->groupBy('shelf', 'status')
            ->get();

        return $rows
            ->groupBy('shelf')
            ->map(function (Collection $group, string $shelf): array {
                $counts = $group->mapWithKeys(function ($row): array {
                    $status = $row->status instanceof \BackedEnum ? $row->status->value : (string) $row->status;

                    return [$status => (int) $row->aggregate];
                });

                return [
                    'location' => $shelf,
                    'total' => (int) $group->sum('aggregate'),
                    'available' => (int) ($counts['available'] ?? 0),
                    'borrowed' => (int) ($counts['borrowed'] ?? 0),
                    'maintenance' => (int) ($counts['maintenance'] ?? 0),
                ];
            })
            ->sortBy('location')
            ->values();
    }
}
