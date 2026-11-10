<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\BookingStatus;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalQueueService
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly AvailabilityService $availability,
        private readonly SettingService $settings,
    ) {}

    /**
     * @param  array{search?: string|null, category_id?: int|null, urgency?: string|null, sort?: string|null}  $filters
     * @return array{cards: Collection<int, array<string, mixed>>, kpis: array<string, int|float>, filters: array<string, mixed>, categories: Collection<int, \App\Models\Category>}
     */
    public function for(User $user, array $filters = []): array
    {
        $organizationId = $user->organization_id;

        $query = Booking::query()
            ->with(['borrower', 'items.asset.category'])
            ->where('organization_id', $organizationId)
            ->where('status', BookingStatus::Pending->value)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhereHas('borrower', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, int $categoryId) => $query->whereHas(
                'items.asset',
                fn ($query) => $query->where('category_id', $categoryId),
            ));

        $this->order($query, $filters['sort'] ?? 'nearest', $filters['urgency'] ?? null);

        $cards = $query->get()
            ->map(fn (Booking $booking): array => $this->card($user, $booking))
            ->values();

        $conflicts = $cards->where('has_conflict', true)->count();
        $special = $cards->where('is_special', true)->count();
        $decided = Booking::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Rejected->value])
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $approved = (clone $decided)->where('status', BookingStatus::Approved->value)->count();
        $total = (clone $decided)->count();

        return [
            'cards' => $cards,
            'kpis' => [
                'waiting' => $cards->count(),
                'conflicts' => $conflicts,
                'special' => $special,
                'approval_rate' => $total > 0 ? round($approved / $total * 100, 1) : 0,
            ],
            'filters' => $filters,
            'policy' => [
                'max_days' => $this->settings->maxLoanDays($organizationId),
                'operator_limit' => $this->settings->operatorApprovalLimit($organizationId),
            ],
            'categories' => \App\Models\Category::query()
                ->where('organization_id', $organizationId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function card(User $user, Booking $booking): array
    {
        $booking->loadMissing(['borrower', 'items.asset.category']);

        $assets = $booking->items->map->asset->filter();
        $conflict = $this->bookings->conflictMessageFor(
            $assets,
            $booking->requested_start_date,
            $booking->requested_end_date,
        );

        $blockReason = $this->bookings->decisionBlockReason($user, $booking);

        return [
            'booking' => $booking,
            'has_conflict' => $conflict !== null,
            'conflict' => $conflict,
            'alternatives' => $conflict === null ? [] : $this->alternativesFor($booking),
            'block_reason' => $blockReason,
            'can_decide' => $blockReason === null,
            'is_special' => $booking->isLongDuration($this->settings->maxLoanDays($booking->organization_id)),
            'value' => $booking->totalAssetValue(),
            'urgency' => $booking->requested_start_date->isBefore(now()->addDays(2))
                ? 'high'
                : ($booking->requested_start_date->isBefore(now()->addWeek()) ? 'medium' : 'low'),
        ];
    }

    /**
     * @return array<int, array{booking_item_id: int, asset_id: int, code: string, name: string}>
     */
    public function alternativesFor(Booking $booking): array
    {
        $booking->loadMissing('items.asset');
        $bookingAssetIds = $booking->items->pluck('asset_id')->all();
        $alternatives = [];

        foreach ($booking->items as $item) {
            $asset = $item->asset;

            if ($asset === null) {
                continue;
            }

            $candidate = Asset::query()
                ->where('organization_id', $booking->organization_id)
                ->where('category_id', $asset->category_id)
                ->where('status', AssetStatus::Available->value)
                ->whereKeyNot($bookingAssetIds)
                ->orderBy('code')
                ->get()
                ->first(fn (Asset $candidate): bool => $this->availability->isAvailableForRange(
                    $candidate,
                    $booking->requested_start_date,
                    $booking->requested_end_date,
                ));

            if ($candidate !== null) {
                $alternatives[] = [
                    'booking_item_id' => $item->id,
                    'asset_id' => $candidate->id,
                    'code' => $candidate->code,
                    'name' => $candidate->name,
                ];
            }
        }

        return $alternatives;
    }

    private function order($query, string $sort, ?string $urgency): void
    {
        match ($sort) {
            'newest' => $query->latest('submitted_at')->latest(),
            default => $query->orderBy('requested_start_date'),
        };

        if ($urgency === 'high') {
            $query->whereDate('requested_start_date', '<=', now()->addDays(2)->toDateString());
        } elseif ($urgency === 'medium') {
            $query->whereDate('requested_start_date', '<=', now()->addWeek()->toDateString());
        }
    }
}
