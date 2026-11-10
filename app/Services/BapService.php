<?php

namespace App\Services;

use App\Enums\PenaltyStatus;
use App\Models\Booking;
use App\Models\Penalty;
use App\Models\PenaltyItem;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BapService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $actor, array $data): Penalty
    {
        $this->assertStaff($actor, (int) $data['organization_id']);

        return DB::transaction(function () use ($actor, $data): Penalty {
            $booking = isset($data['booking_id'])
                ? Booking::query()->with('items')->find($data['booking_id'])
                : null;

            if ($booking !== null) {
                if ((int) $booking->organization_id !== (int) $actor->organization_id) {
                    throw new AuthorizationException(__('messages.booking.wrong_organization'));
                }

                if ((int) $booking->borrower_id !== (int) $data['borrower_id']) {
                    throw ValidationException::withMessages([
                        'borrower_id' => __('messages.bap.borrower_mismatch'),
                    ]);
                }

                if (isset($data['asset_id'])
                    && ! $booking->items->contains('asset_id', (int) $data['asset_id'])) {
                    throw ValidationException::withMessages([
                        'asset_id' => __('messages.bap.asset_mismatch'),
                    ]);
                }
            }

            $penalty = Penalty::create([
                'organization_id' => $actor->organization_id,
                'booking_id' => $data['booking_id'] ?? null,
                'asset_id' => $data['asset_id'] ?? null,
                'borrower_id' => $data['borrower_id'],
                'bap_number' => $this->generateNumber($actor->organization_id),
                'incident_at' => $data['incident_at'],
                'inspector_name' => $data['inspector_name'],
                'damage_description' => $data['damage_description'],
                'case_category' => $data['case_category'] ?? null,
                'chronology' => $data['chronology'] ?? null,
                'maintenance_id' => $data['maintenance_id'] ?? null,
                'subsidy_percent' => $data['subsidy_percent']
                    ?? (int) $this->settings->get('damage_subsidy_percent', 10, $actor->organization_id),
                'admin_fee' => (int) ($data['admin_fee'] ?? 0),
                'payment_terms_days' => (int) ($data['payment_terms_days'] ?? 7),
                'repair_subtotal' => 0,
                'subsidy_amount' => 0,
                'total_amount' => 0,
                'status' => PenaltyStatus::WaitingPayment,
                'due_at' => now()->addDays((int) ($data['payment_terms_days'] ?? 7)),
                'recovery_step' => 1,
                'photo_paths' => $data['photo_paths'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            foreach ($data['items'] ?? [] as $item) {
                $this->createItem($penalty, $item);
            }

            $penalty = $this->recalculate($penalty);

            $this->logger->log(
                'bap.created',
                $penalty,
                "BAP {$penalty->bap_number} dibuat.",
                $actor,
                ['total_amount' => $penalty->total_amount],
            );

            return $penalty->fresh(['items', 'borrower', 'asset', 'booking', 'maintenance']);
        });
    }

    public function recalculate(Penalty $penalty): Penalty
    {
        $subtotal = (int) $penalty->items()->sum('subtotal');
        $subsidyAmount = (int) round($subtotal * $penalty->subsidy_percent / 100);

        $penalty->update([
            'repair_subtotal' => $subtotal,
            'subsidy_amount' => $subsidyAmount,
            'total_amount' => max(0, $subtotal - $subsidyAmount + $penalty->admin_fee),
        ]);

        return $penalty->fresh(['items']);
    }

    public function issue(Penalty $penalty, User $actor): Penalty
    {
        $this->assertStaff($actor, $penalty->organization_id);

        if ($penalty->issued_at !== null) {
            throw ValidationException::withMessages([
                'status' => __('messages.bap.already_issued'),
            ]);
        }

        if (in_array($penalty->status, [PenaltyStatus::Paid, PenaltyStatus::Waived], true)) {
            throw ValidationException::withMessages([
                'status' => __('messages.bap.not_issuable'),
            ]);
        }

        return DB::transaction(function () use ($penalty, $actor): Penalty {
            $penalty = Penalty::query()->whereKey($penalty->id)->lockForUpdate()->firstOrFail();
            $penalty = $this->recalculate($penalty);

            $penalty->update([
                'issued_at' => now(),
                'issued_by' => $actor->id,
                'due_at' => now()->addDays($penalty->payment_terms_days),
                'status' => PenaltyStatus::WaitingPayment,
            ]);

            $suspended = $this->suspendBorrower($penalty, $actor);

            $this->logger->log(
                'bap.issued',
                $penalty,
                "BAP {$penalty->bap_number} diterbitkan.",
                $actor,
                ['total_amount' => $penalty->total_amount, 'borrower_suspended' => $suspended],
            );

            return $penalty->fresh(['items', 'borrower', 'asset', 'booking', 'maintenance']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addItem(Penalty $penalty, User $actor, array $data): PenaltyItem
    {
        $this->assertStaff($actor, $penalty->organization_id);

        if (in_array($penalty->status, [PenaltyStatus::Paid, PenaltyStatus::Waived], true)) {
            throw ValidationException::withMessages([
                'items' => __('messages.bap.not_editable'),
            ]);
        }

        return DB::transaction(function () use ($penalty, $actor, $data): PenaltyItem {
            $item = $this->createItem($penalty, $data);
            $penalty = $this->recalculate($penalty);

            $this->logger->log(
                'bap.item_added',
                $penalty,
                "Rincian biaya {$item->description} ditambahkan ke BAP {$penalty->bap_number}.",
                $actor,
                ['item_id' => $item->id, 'total_amount' => $penalty->total_amount],
            );

            return $item;
        });
    }

    public function generateNumber(int $organizationId): string
    {
        $prefix = (string) $this->settings->get('bap_prefix', 'LDR-BAP', $organizationId);
        $year = now()->year;
        $base = "{$prefix}-{$year}-";

        $last = Penalty::query()
            ->where('bap_number', 'like', $base.'%')
            ->orderByDesc('bap_number')
            ->value('bap_number');

        $sequence = $last === null ? 1 : ((int) substr((string) $last, strlen($base))) + 1;

        return sprintf('%s%04d', $base, $sequence);
    }

    private function suspendBorrower(Penalty $penalty, User $actor): bool
    {
        if ((string) $this->settings->get('suspension_on_penalty', '1', $penalty->organization_id) !== '1') {
            return false;
        }

        $borrower = $penalty->borrower;

        if ($borrower === null || $borrower->status !== 'active' || ! $borrower->hasRole('borrower')) {
            return false;
        }

        $penaltyScore = (int) $this->settings->get('bap_compliance_penalty', '20', $penalty->organization_id);

        $borrower->update([
            'status' => 'suspended',
            'compliance_score' => max(0, (int) ($borrower->compliance_score ?? 100) - $penaltyScore),
        ]);

        $this->logger->log(
            'user.suspended',
            $borrower,
            "Akun {$borrower->name} ditangguhkan otomatis karena BAP {$penalty->bap_number}.",
            $actor,
            ['bap_number' => $penalty->bap_number],
        );

        return true;
    }

    private function assertStaff(User $actor, int $organizationId): void
    {
        if ((int) $actor->organization_id !== $organizationId
            || ! in_array($actor->role?->slug, ['operator', 'organization_admin'], true)
        ) {
            throw new AuthorizationException(__('messages.bap.not_allowed'));
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function createItem(Penalty $penalty, array $item): PenaltyItem
    {
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $unitCost = max(0, (int) ($item['unit_cost'] ?? 0));

        return $penalty->items()->create([
            'description' => $item['description'],
            'part_code' => $item['part_code'] ?? null,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'subtotal' => $quantity * $unitCost,
        ]);
    }
}
