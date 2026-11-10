<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Enums\BookingItemStatus;
use App\Enums\BookingStatus;
use App\Enums\HandoverType;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Handover;
use App\Models\User;
use App\Notifications\LoanPickupNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HandoverService
{
    public function __construct(private readonly ActivityLogger $logger) {}

    /**
     * @param  array<int|string, string>  $itemConditions  condition keyed by booking_item id
     */
    public function recordPickup(
        Booking $booking,
        User $operator,
        array $itemConditions,
        ?string $notes = null,
        array $options = [],
    ): Booking {
        $this->assertStaff($operator, $booking);

        return DB::transaction(function () use ($booking, $operator, $itemConditions, $notes, $options): Booking {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->status !== BookingStatus::Approved) {
                throw ValidationException::withMessages([
                    'status' => __('messages.handover.pickup_status'),
                ]);
            }

            $booking->load('items.asset');
            $conditions = $this->conditionsFor($booking, $itemConditions);
            $happenedAt = now();

            $booking->update([
                'status' => BookingStatus::Active,
                'pickup_at' => $happenedAt,
                'due_at' => $booking->due_at ?? $booking->requested_end_date->copy()->setTime(16, 0),
            ]);

            foreach ($booking->items as $item) {
                $item->update([
                    'status' => BookingItemStatus::PickedUp,
                    'condition_out' => $conditions[$item->id],
                ]);

                Asset::query()
                    ->whereKey($item->asset_id)
                    ->where('status', AssetStatus::Reserved->value)
                    ->update(['status' => AssetStatus::Borrowed->value]);
            }

            $handover = $booking->handovers()->create([
                'code' => $options['code'] ?? $this->receiptCode($booking, HandoverType::Pickup),
                'type' => HandoverType::Pickup,
                'performed_by' => $operator->id,
                'shift_label' => $options['shift_label'] ?? null,
                'borrower_signature_path' => $options['borrower_signature_path'] ?? null,
                'operator_signature_path' => $options['operator_signature_path'] ?? null,
                'photo_paths' => $options['photo_paths'] ?? null,
                'checklist' => $options['checklist'] ?? null,
                'happened_at' => $happenedAt,
                'due_at' => $booking->due_at,
                'notes' => $notes,
            ]);

            $this->storeItems($handover, $booking, $conditions, $options);

            $this->logger->log(
                'handover.pickup',
                $booking,
                "Serah terima ambil {$booking->code} dicatat.",
                $operator,
                ['conditions' => $conditions],
            );

            $booking->borrower->notify(new LoanPickupNotification($booking->fresh()));

            return $booking->fresh(['items.asset', 'handovers.performedBy', 'borrower']);
        });
    }

    /**
     * @param  array<int|string, string>  $itemConditions  condition keyed by booking_item id
     */
    public function recordReturn(
        Booking $booking,
        User $operator,
        array $itemConditions,
        ?string $notes = null,
        array $options = [],
    ): Booking {
        $this->assertStaff($operator, $booking);

        return DB::transaction(function () use ($booking, $operator, $itemConditions, $notes, $options): Booking {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->status !== BookingStatus::Active) {
                throw ValidationException::withMessages([
                    'status' => __('messages.handover.return_status'),
                ]);
            }

            $booking->load('items.asset');
            $conditions = $this->conditionsFor($booking, $itemConditions);
            $happenedAt = now();

            foreach ($booking->items as $item) {
                $condition = AssetCondition::from($conditions[$item->id]);

                $item->update([
                    'status' => match ($condition) {
                        AssetCondition::Lost => BookingItemStatus::Lost,
                        AssetCondition::Damaged => BookingItemStatus::Damaged,
                        default => BookingItemStatus::Returned,
                    },
                    'condition_in' => $condition->value,
                ]);
            }

            $booking->update([
                'status' => BookingStatus::Returned,
                'returned_at' => $happenedAt,
            ]);

            // Asset status is decided by inspection, never by the return handover itself.
            $handover = $booking->handovers()->create([
                'code' => $options['code'] ?? $this->receiptCode($booking, HandoverType::Return),
                'type' => HandoverType::Return,
                'performed_by' => $operator->id,
                'shift_label' => $options['shift_label'] ?? null,
                'borrower_signature_path' => $options['borrower_signature_path'] ?? null,
                'operator_signature_path' => $options['operator_signature_path'] ?? null,
                'photo_paths' => $options['photo_paths'] ?? null,
                'checklist' => $options['checklist'] ?? null,
                'happened_at' => $happenedAt,
                'due_at' => $booking->due_at,
                'notes' => $notes,
            ]);

            $this->storeItems($handover, $booking, $conditions, $options);

            $this->logger->log(
                'handover.return',
                $booking,
                "Serah terima kembali {$booking->code} dicatat.",
                $operator,
                ['conditions' => $conditions],
            );

            return $booking->fresh(['items.asset', 'handovers.performedBy', 'borrower']);
        });
    }

    private function receiptCode(Booking $booking, HandoverType $type): string
    {
        $suffix = $type === HandoverType::Pickup ? 'OUT' : 'IN';

        return sprintf('TRX-%d-%04d-%s', now()->year, $booking->id, $suffix);
    }

    private function storeItems(Handover $handover, Booking $booking, array $conditions, array $options): void
    {
        foreach ($booking->items as $item) {
            $handover->items()->create([
                'booking_item_id' => $item->id,
                'condition' => $conditions[$item->id],
                'accessories' => $options['accessories'][$item->id]
                    ?? $options['accessories'][(string) $item->id]
                    ?? null,
                'notes' => $options['item_notes'][$item->id]
                    ?? $options['item_notes'][(string) $item->id]
                    ?? null,
            ]);
        }
    }

    private function assertStaff(User $operator, Booking $booking): void
    {
        if ($operator->organization_id !== $booking->organization_id) {
            throw new AuthorizationException(__('messages.booking.wrong_organization'));
        }

        if (! in_array($operator->role?->slug, ['operator', 'organization_admin'], true)) {
            throw new AuthorizationException(__('messages.handover.not_allowed'));
        }
    }

    /**
     * @param  array<int|string, string>  $input
     * @return array<int, string>
     */
    private function conditionsFor(Booking $booking, array $input): array
    {
        $conditions = [];

        foreach ($booking->items as $item) {
            $value = $input[$item->id] ?? $input[(string) $item->id] ?? null;

            if ($value === null || AssetCondition::tryFrom((string) $value) === null) {
                throw ValidationException::withMessages([
                    "conditions.{$item->id}" => __('messages.handover.condition_required'),
                ]);
            }

            $conditions[$item->id] = (string) $value;
        }

        return $conditions;
    }
}
