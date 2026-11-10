<?php

namespace App\Services;

use App\Enums\AssetCondition;
use App\Enums\BookingStatus;
use App\Enums\HandoverType;
use App\Enums\InspectionResult;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class KioskService
{
    public function __construct(
        private readonly HandoverService $handovers,
        private readonly InspectionService $inspections,
        private readonly ActivityLogger $logger,
    ) {}

    /**
     * @return array{booking: Booking, type: HandoverType, allowed: bool, reason: ?string}
     */
    public function lookup(string $query, User $actor): array
    {
        $query = trim($query);
        $organizationId = $actor->organization_id;

        $booking = Booking::query()
            ->with(['borrower', 'items.asset'])
            ->where('organization_id', $organizationId)
            ->where(function ($builder) use ($query): void {
                $builder->where('code', $query)->orWhere('return_token', $query);
            })
            ->first();

        if ($booking === null) {
            $user = User::query()
                ->where('organization_id', $organizationId)
                ->where('nim', $query)
                ->first();

            if ($user !== null) {
                $booking = Booking::query()
                    ->with(['borrower', 'items.asset'])
                    ->where('organization_id', $organizationId)
                    ->where('borrower_id', $user->id)
                    ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Active->value])
                    ->latest('requested_start_date')
                    ->first();
            }
        }

        if ($booking === null) {
            $asset = Asset::query()
                ->where('organization_id', $organizationId)
                ->where('code', $query)
                ->first();

            if ($asset !== null) {
                $booking = Booking::query()
                    ->with(['borrower', 'items.asset'])
                    ->where('organization_id', $organizationId)
                    ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Active->value])
                    ->whereHas('items', fn ($builder) => $builder->where('asset_id', $asset->id))
                    ->latest('requested_start_date')
                    ->first();
            }
        }

        if ($booking === null) {
            throw ValidationException::withMessages([
                'query' => __('messages.kiosk.not_found'),
            ]);
        }

        $type = $booking->status === BookingStatus::Active ? HandoverType::Return : HandoverType::Pickup;
        $preflight = $this->preflight($booking, $type);

        $this->logger->log('kiosk.scanned', $booking, "Pemindaian kiosk untuk {$booking->code}.", $actor, [
            'query' => $query,
            'suggested_type' => $type->value,
        ]);

        return [
            'booking' => $booking,
            'type' => $type,
            'allowed' => $preflight['allowed'],
            'reason' => $preflight['reason'],
        ];
    }

    /**
     * @return array{allowed: bool, reason: ?string}
     */
    public function preflight(Booking $booking, HandoverType $type): array
    {
        if ($type === HandoverType::Pickup && $booking->status !== BookingStatus::Approved) {
            return ['allowed' => false, 'reason' => __('messages.handover.pickup_status')];
        }

        if ($type === HandoverType::Return && $booking->status !== BookingStatus::Active) {
            return ['allowed' => false, 'reason' => __('messages.handover.return_status')];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function process(
        Booking $booking,
        User $actor,
        HandoverType $type,
        array $conditions,
        array $options = [],
    ): Booking {
        $preflight = $this->preflight($booking, $type);

        if (! $preflight['allowed']) {
            throw ValidationException::withMessages(['type' => $preflight['reason']]);
        }

        if ($type === HandoverType::Pickup) {
            $booking = $this->handovers->recordPickup($booking, $actor, $conditions, $options['notes'] ?? null, $options);

            $this->logger->log('kiosk.pickup', $booking, "Pickup kiosk {$booking->code} diproses.", $actor);

            return $booking;
        }

        $booking = $this->handovers->recordReturn($booking, $actor, $conditions, $options['notes'] ?? null, $options);

        if ($options['inspect'] ?? true) {
            foreach ($booking->items as $item) {
                $this->inspections->record(
                    $item->asset,
                    $actor,
                    $item,
                    $this->resultFor(AssetCondition::from($conditions[$item->id])),
                    $options['inspection_notes'] ?? null,
                    [
                        'context' => 'post_return',
                        'checklist' => $options['checklist'] ?? null,
                    ],
                );
            }
        }

        $this->logger->log('kiosk.return', $booking, "Return kiosk {$booking->code} diproses.", $actor);

        return $booking->fresh(['items.asset', 'handovers.performedBy', 'borrower']);
    }

    private function resultFor(AssetCondition $condition): string
    {
        return match ($condition) {
            AssetCondition::Good => InspectionResult::Ok->value,
            AssetCondition::MinorDamage => InspectionResult::NeedsRepair->value,
            AssetCondition::Damaged, AssetCondition::Lost => InspectionResult::Damaged->value,
        };
    }
}
