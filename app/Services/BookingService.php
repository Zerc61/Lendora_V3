<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\BookingItemStatus;
use App\Enums\BookingStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\BookingDecisionNotification;
use App\Notifications\BookingReceivedNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly SettingService $settings,
        private readonly ActivityLogger $logger,
    ) {}

    /**
     * @param  array<int, int|string>  $assetIds
     */
    public function createRequest(
        User $borrower,
        array $assetIds,
        CarbonInterface $start,
        CarbonInterface $end,
        string $purpose,
    ): Booking {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->startOfDay();
        $assetIds = array_values(array_unique(array_map('intval', $assetIds)));

        if ($assetIds === []) {
            throw ValidationException::withMessages(['assets' => __('messages.booking.assets_required')]);
        }

        if ($end->lt($start)) {
            throw ValidationException::withMessages(['requested_end_date' => __('messages.booking.invalid_range')]);
        }

        $maxLoanDays = $this->settings->maxLoanDays();

        if ((int) $start->diffInDays($end) > $maxLoanDays) {
            throw ValidationException::withMessages([
                'requested_end_date' => __('messages.booking.duration_exceeded', ['days' => $maxLoanDays]),
            ]);
        }

        return DB::transaction(function () use ($borrower, $assetIds, $start, $end, $purpose): Booking {
            $assets = Asset::query()
                ->where('organization_id', $borrower->organization_id)
                ->whereIn('id', $assetIds)
                ->lockForUpdate()
                ->get();

            if ($assets->count() !== count($assetIds)) {
                throw ValidationException::withMessages(['assets' => __('messages.booking.assets_not_found')]);
            }

            $conflict = $this->conflictMessageFor($assets, $start, $end);

            if ($conflict !== null) {
                throw ValidationException::withMessages(['assets' => $conflict]);
            }

            $booking = $this->persistBooking($borrower->organization, [
                'borrower_id' => $borrower->id,
                'status' => BookingStatus::Pending,
                'purpose' => $purpose,
                'requested_start_date' => $start->toDateString(),
                'requested_end_date' => $end->toDateString(),
            ], $start->year);

            foreach ($assets as $asset) {
                $booking->items()->create([
                    'asset_id' => $asset->id,
                    'status' => BookingItemStatus::Pending,
                ]);
            }

            $this->logger->log(
                'booking.created',
                $booking,
                "Pengajuan peminjaman {$booking->code} dibuat.",
                $borrower,
                ['asset_ids' => $assetIds, 'start' => $start->toDateString(), 'end' => $end->toDateString()],
            );

            $borrower->notify(new BookingReceivedNotification($booking));

            return $booking->load('items.asset');
        });
    }

    public function approve(Booking $booking, User $approver): Booking
    {
        $this->assertTransition($booking, BookingStatus::Approved);
        $this->assertCanDecide($approver, $booking);

        return DB::transaction(function () use ($booking, $approver): Booking {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertTransition($booking, BookingStatus::Approved);

            $booking->load('items.asset');

            $assets = Asset::query()
                ->whereIn('id', $booking->items->pluck('asset_id'))
                ->lockForUpdate()
                ->get();

            $conflict = $this->conflictMessageFor($assets, $booking->requested_start_date, $booking->requested_end_date);

            if ($conflict !== null) {
                throw ValidationException::withMessages(['assets' => $conflict]);
            }

            $booking->update([
                'status' => BookingStatus::Approved,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'due_at' => $booking->requested_end_date->copy()->setTime(16, 0),
            ]);

            $booking->items()->update(['status' => BookingItemStatus::Approved]);

            Asset::query()
                ->whereIn('id', $booking->items->pluck('asset_id'))
                ->update(['status' => AssetStatus::Reserved->value]);

            $this->logger->log(
                'booking.approved',
                $booking,
                "Pengajuan peminjaman {$booking->code} disetujui.",
                $approver,
            );

            $booking->borrower->notify(new BookingDecisionNotification($booking->fresh(), true));

            return $booking->fresh(['items.asset', 'borrower']);
        });
    }

    public function reject(Booking $booking, User $rejecter, string $reason): Booking
    {
        $this->assertTransition($booking, BookingStatus::Rejected);

        if (trim($reason) === '') {
            throw ValidationException::withMessages(['rejection_reason' => __('messages.booking.reason_required')]);
        }

        $role = $rejecter->role?->slug;

        if (! in_array($role, ['operator', 'organization_admin'], true)) {
            throw new AuthorizationException(__('messages.booking.reject_not_allowed'));
        }

        if ($role === 'operator' && ($block = $this->operatorBlockReason($booking)) !== null) {
            throw new AuthorizationException($block);
        }

        return DB::transaction(function () use ($booking, $rejecter, $reason): Booking {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertTransition($booking, BookingStatus::Rejected);

            $booking->update([
                'status' => BookingStatus::Rejected,
                'rejection_reason' => $reason,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $this->logger->log(
                'booking.rejected',
                $booking,
                "Pengajuan peminjaman {$booking->code} ditolak: {$reason}",
                $rejecter,
            );

            $booking->borrower->notify(new BookingDecisionNotification($booking->fresh(), false, $reason));

            return $booking->fresh(['items.asset', 'borrower']);
        });
    }

    public function cancel(Booking $booking, User $actor): Booking
    {
        $this->assertTransition($booking, BookingStatus::Cancelled);

        $isOwner = $actor->id === $booking->borrower_id;
        $isStaff = in_array($actor->role?->slug, ['operator', 'organization_admin'], true);
        $pendingByOwner = $booking->status === BookingStatus::Pending && $isOwner;
        $approvedNotPickedUp = $booking->status === BookingStatus::Approved
            && $isStaff
            && $booking->pickup_at === null;

        if (! $pendingByOwner && ! $approvedNotPickedUp) {
            throw new AuthorizationException(__('messages.booking.cancel_not_allowed'));
        }

        return DB::transaction(function () use ($booking, $actor): Booking {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->assertTransition($booking, BookingStatus::Cancelled);

            $wasApproved = $booking->status === BookingStatus::Approved;

            $booking->update(['status' => BookingStatus::Cancelled]);

            if ($wasApproved) {
                Asset::query()
                    ->whereIn('id', $booking->items()->pluck('asset_id'))
                    ->where('status', AssetStatus::Reserved->value)
                    ->update(['status' => AssetStatus::Available->value]);

                $booking->items()->update(['status' => BookingItemStatus::Pending]);
            }

            $this->logger->log(
                'booking.cancelled',
                $booking,
                "Peminjaman {$booking->code} dibatalkan.",
                $actor,
            );

            return $booking->fresh(['items.asset', 'borrower']);
        });
    }

    /**
     * @return array<int, array{status: string, actor: string|null, time: \Illuminate\Support\Carbon|null, note: string|null}>
     */
    public function timelineFor(Booking $booking): array
    {
        $events = [[
            'status' => BookingStatus::Pending->value,
            'actor' => $booking->borrower?->name,
            'time' => $booking->created_at,
            'note' => __('messages.booking.timeline_created'),
        ]];

        if ($booking->approved_at !== null) {
            $events[] = [
                'status' => BookingStatus::Approved->value,
                'actor' => $booking->approvedBy?->name,
                'time' => $booking->approved_at,
                'note' => __('messages.booking.timeline_approved'),
            ];
        }

        if ($booking->pickup_at !== null) {
            $events[] = [
                'status' => BookingStatus::Active->value,
                'actor' => null,
                'time' => $booking->pickup_at,
                'note' => __('messages.booking.timeline_picked_up'),
            ];
        }

        if ($booking->returned_at !== null) {
            $events[] = [
                'status' => BookingStatus::Returned->value,
                'actor' => null,
                'time' => $booking->returned_at,
                'note' => __('messages.booking.timeline_returned'),
            ];
        }

        if ($booking->status === BookingStatus::Rejected) {
            $events[] = [
                'status' => BookingStatus::Rejected->value,
                'actor' => $booking->approvedBy?->name,
                'time' => $booking->updated_at,
                'note' => $booking->rejection_reason,
            ];
        }

        if ($booking->status === BookingStatus::Cancelled) {
            $events[] = [
                'status' => BookingStatus::Cancelled->value,
                'actor' => null,
                'time' => $booking->updated_at,
                'note' => __('messages.booking.timeline_cancelled'),
            ];
        }

        return $events;
    }

    public function decisionBlockReason(User $user, Booking $booking): ?string
    {
        if ($user->organization_id !== $booking->organization_id) {
            return __('messages.booking.wrong_organization');
        }

        return match ($user->role?->slug) {
            'organization_admin' => null,
            'operator' => $this->operatorBlockReason($booking),
            'admin_prodi' => $booking->isLongDuration($this->settings->maxLoanDays())
                ? null
                : __('messages.booking.prodi_short_request', ['days' => $this->settings->maxLoanDays()]),
            default => __('messages.booking.not_allowed'),
        };
    }

    public function operatorBlockReason(Booking $booking): ?string
    {
        $maxLoanDays = $this->settings->maxLoanDays();
        $booking->loadMissing('items.asset');

        if ($booking->isLongDuration($maxLoanDays)) {
            return __('messages.booking.operator_duration_limit', ['days' => $maxLoanDays]);
        }

        $limit = $this->settings->operatorApprovalLimit();

        if ($booking->totalAssetValue() > $limit) {
            return __('messages.booking.operator_value_limit', [
                'limit' => number_format($limit, 0, ',', '.'),
            ]);
        }

        return null;
    }

    private function assertCanDecide(User $user, Booking $booking): void
    {
        $reason = $this->decisionBlockReason($user, $booking);

        if ($reason !== null) {
            throw new AuthorizationException($reason);
        }
    }

    private function assertTransition(Booking $booking, BookingStatus $to): void
    {
        if ($booking->status === $to) {
            return;
        }

        $allowed = match ($booking->status) {
            BookingStatus::Pending => [BookingStatus::Approved, BookingStatus::Rejected, BookingStatus::Cancelled],
            BookingStatus::Approved => [BookingStatus::Active, BookingStatus::Cancelled],
            BookingStatus::Active => [BookingStatus::Returned],
            default => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw InvalidStatusTransitionException::for($booking, $to);
        }
    }

    public function conflictMessageFor(iterable $assets, CarbonInterface $start, CarbonInterface $end): ?string
    {
        foreach ($assets as $asset) {
            $conflict = $this->availability->conflictsFor($asset, $start, $end)->orderBy('requested_start_date')->first();

            if ($conflict !== null) {
                $suggestion = $this->availability->suggestFreeRange($asset, $start, $end);

                return __('messages.booking.conflict', [
                    'asset' => "{$asset->code} {$asset->name}",
                    'start' => $start->copy()->locale('id')->translatedFormat('d M Y'),
                    'end' => $end->copy()->locale('id')->translatedFormat('d M Y'),
                    'code' => $conflict->code,
                    'suggestion' => $suggestion?->copy()->locale('id')->translatedFormat('d M Y')
                        ?? __('messages.booking.no_suggestion'),
                ]);
            }

            if ($asset->status !== AssetStatus::Available) {
                return __('messages.booking.asset_unavailable', [
                    'asset' => "{$asset->code} {$asset->name}",
                    'status' => $asset->status->value,
                ]);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistBooking(Organization $organization, array $data, int $year): Booking
    {
        $prefix = $organization->code.'-BK-'.$year.'-';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $last = Booking::withTrashed()
                ->where('code', 'like', $prefix.'%')
                ->orderByDesc('code')
                ->value('code');

            $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
            $code = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);

            try {
                return Booking::create([
                    ...$data,
                    'organization_id' => $organization->id,
                    'code' => $code,
                ]);
            } catch (QueryException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Gagal membuat kode peminjaman unik.');
    }
}
