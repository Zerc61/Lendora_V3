<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Enums\BookingExtensionStatus;
use App\Enums\BookingItemStatus;
use App\Enums\BookingStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingItem;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\BookingAlternativeNotification;
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
     * @return array<int, array{booking_item_id: int, asset_id: int, code: string, name: string}>
     */
    public function suggestAlternative(Booking $booking, User $actor): array
    {
        $this->assertStaffForBooking($booking, $actor);

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'booking' => __('messages.booking.suggest_not_allowed'),
            ]);
        }

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

        if ($alternatives === []) {
            throw ValidationException::withMessages([
                'booking' => __('messages.booking.no_alternative'),
            ]);
        }

        $this->logger->log(
            'booking.suggested_alternative',
            $booking,
            "Unit alternatif disarankan untuk {$booking->code}.",
            $actor,
            ['alternatives' => $alternatives],
        );

        $booking->borrower->notify(new BookingAlternativeNotification($booking, $alternatives));

        return $alternatives;
    }

    public function reassignItem(Booking $booking, BookingItem $item, Asset $replacement, User $actor): BookingItem
    {
        $this->assertStaffForBooking($booking, $actor);

        if ($item->booking_id !== $booking->id) {
            throw new AuthorizationException(__('messages.booking.item_mismatch'));
        }

        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'asset_id' => __('messages.booking.reassign_not_allowed'),
            ]);
        }

        return DB::transaction(function () use ($booking, $item, $replacement, $actor): BookingItem {
            $booking = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $replacement = Asset::query()->whereKey($replacement->id)->lockForUpdate()->firstOrFail();
            $item = BookingItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $current = Asset::query()->whereKey($item->asset_id)->lockForUpdate()->firstOrFail();

            if ((int) $replacement->organization_id !== (int) $booking->organization_id) {
                throw ValidationException::withMessages(['asset_id' => __('messages.booking.assets_not_found')]);
            }

            if ((int) $replacement->category_id !== (int) $current->category_id) {
                throw ValidationException::withMessages(['asset_id' => __('messages.booking.reassign_category')]);
            }

            if ((int) $replacement->id === (int) $current->id) {
                throw ValidationException::withMessages(['asset_id' => __('messages.booking.reassign_same')]);
            }

            if ($booking->items()->where('asset_id', $replacement->id)->exists()) {
                throw ValidationException::withMessages(['asset_id' => __('messages.booking.reassign_duplicate')]);
            }

            $conflict = $this->conflictMessageFor(
                [$replacement],
                $booking->requested_start_date,
                $booking->requested_end_date,
            );

            if ($conflict !== null) {
                throw ValidationException::withMessages(['asset_id' => $conflict]);
            }

            if ($booking->status === BookingStatus::Approved) {
                $current->update(['status' => AssetStatus::Available]);
                $replacement->update(['status' => AssetStatus::Reserved]);
            }

            $item->update(['asset_id' => $replacement->id]);

            $this->logger->log(
                'booking.reassigned',
                $booking,
                "Unit {$current->code} dialihkan ke {$replacement->code} pada {$booking->code}.",
                $actor,
                [
                    'booking_item_id' => $item->id,
                    'from_asset_id' => $current->id,
                    'to_asset_id' => $replacement->id,
                ],
            );

            return $item->fresh(['asset']);
        });
    }

    /**
     * @param  array<int, int|string>  $assetIds
     */
    public function createWalkIn(
        User $operator,
        int $borrowerId,
        array $assetIds,
        CarbonInterface $start,
        CarbonInterface $end,
        string $purpose,
    ): Booking {
        if (! in_array($operator->role?->slug, ['operator', 'organization_admin'], true)) {
            throw new AuthorizationException(__('messages.booking.walk_in_not_allowed'));
        }

        $borrower = User::query()
            ->where('organization_id', $operator->organization_id)
            ->whereKey($borrowerId)
            ->whereHas('role', fn ($query) => $query->where('slug', 'borrower'))
            ->firstOrFail();

        if ($borrower->status !== 'active') {
            throw ValidationException::withMessages([
                'borrower_id' => __('messages.booking.walk_in_borrower_inactive'),
            ]);
        }

        $booking = $this->createRequest($borrower, $assetIds, $start, $end, $purpose);

        $this->logger->log(
            'booking.walk_in',
            $booking,
            "Peminjaman walk-in {$booking->code} dibuat operator untuk {$borrower->name}.",
            $operator,
        );

        if ($this->decisionBlockReason($operator, $booking) === null) {
            try {
                $booking = $this->approve($booking, $operator);
            } catch (ValidationException) {
                // Availability or limit changed between creation and approval.
            }
        }

        return $booking->fresh(['items.asset', 'borrower']);
    }

    private function assertStaffForBooking(Booking $booking, User $actor): void
    {
        if ((int) $actor->organization_id !== (int) $booking->organization_id
            || ! in_array($actor->role?->slug, ['operator', 'organization_admin'], true)
        ) {
            throw new AuthorizationException(__('messages.booking.not_allowed'));
        }
    }

    public function requestExtension(
        Booking $booking,
        User $requester,
        CarbonInterface $newEnd,
        ?string $reason = null,
    ): BookingExtension {
        if ($requester->id !== $booking->borrower_id) {
            throw new AuthorizationException(__('messages.booking.extension_not_owner'));
        }

        if ($booking->status !== BookingStatus::Active) {
            throw ValidationException::withMessages([
                'new_end_date' => __('messages.booking.extension_only_active'),
            ]);
        }

        $currentEnd = Carbon::parse($booking->due_at ?? $booking->requested_end_date)->startOfDay();
        $newEnd = Carbon::parse($newEnd)->startOfDay();

        if (! $newEnd->greaterThan($currentEnd)) {
            throw ValidationException::withMessages([
                'new_end_date' => __('messages.booking.extension_after_current', [
                    'date' => $currentEnd->translatedFormat('d M Y'),
                ]),
            ]);
        }

        $hasPending = $booking->extensions()
            ->where('status', BookingExtensionStatus::Pending->value)
            ->exists();

        if ($hasPending) {
            throw ValidationException::withMessages([
                'new_end_date' => __('messages.booking.extension_pending_exists'),
            ]);
        }

        foreach ($booking->items()->with('asset')->get() as $item) {
            $asset = $item->asset;

            if ($asset !== null && $this->availability
                ->conflictsFor($asset, $currentEnd, $newEnd, $booking->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'new_end_date' => __('messages.booking.extension_conflict', [
                        'asset' => $asset->name,
                    ]),
                ]);
            }
        }

        return DB::transaction(function () use ($booking, $requester, $currentEnd, $newEnd, $reason): BookingExtension {
            $extension = $booking->extensions()->create([
                'requested_by' => $requester->id,
                'requested_days' => max(1, (int) $currentEnd->diffInDays($newEnd)),
                'requested_due_at' => $newEnd->copy()->endOfDay(),
                'reason' => $reason ?: __('messages.booking.extension_default_reason'),
                'status' => BookingExtensionStatus::Pending,
            ]);

            $this->logger->log(
                'booking.extension_requested',
                $booking,
                "Pengajuan perpanjangan {$booking->code} sampai {$newEnd->translatedFormat('d M Y')}.",
                $requester,
            );

            return $extension;
        });
    }

    public function approveExtension(BookingExtension $extension, User $approver): BookingExtension
    {
        $this->assertExtensionDecidable($extension, $approver);

        $booking = $extension->booking;
        $start = Carbon::parse($booking->due_at ?? $booking->requested_end_date)->startOfDay();
        $newEnd = Carbon::parse($extension->requested_due_at)->startOfDay();

        foreach ($booking->items()->with('asset')->get() as $item) {
            $asset = $item->asset;

            if ($asset !== null && $this->availability
                ->conflictsFor($asset, $start, $newEnd, $booking->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'extension' => __('messages.booking.extension_conflict', ['asset' => $asset->name]),
                ]);
            }
        }

        return DB::transaction(function () use ($extension, $approver, $booking, $newEnd): BookingExtension {
            $extension->update([
                'status' => BookingExtensionStatus::Approved,
                'decided_by' => $approver->id,
                'decided_at' => now(),
            ]);

            $booking->update([
                'requested_end_date' => $newEnd->toDateString(),
                'due_at' => $newEnd->copy()->endOfDay(),
            ]);

            $this->logger->log(
                'booking.extension_approved',
                $booking,
                "Perpanjangan {$booking->code} disetujui sampai {$newEnd->translatedFormat('d M Y')}.",
                $approver,
            );

            return $extension->fresh();
        });
    }

    public function rejectExtension(BookingExtension $extension, User $approver, string $reason): BookingExtension
    {
        $this->assertExtensionDecidable($extension, $approver);

        $extension->update([
            'status' => BookingExtensionStatus::Rejected,
            'decided_by' => $approver->id,
            'decided_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->logger->log(
            'booking.extension_rejected',
            $extension->booking,
            "Perpanjangan {$extension->booking->code} ditolak: {$reason}.",
            $approver,
        );

        return $extension->fresh();
    }

    private function assertExtensionDecidable(BookingExtension $extension, User $approver): void
    {
        if (! in_array($approver->role?->slug, ['operator', 'organization_admin'], true)) {
            throw new AuthorizationException(__('messages.booking.extension_not_allowed'));
        }

        if ($extension->status !== BookingExtensionStatus::Pending) {
            throw new InvalidStatusTransitionException(__('messages.booking.extension_not_pending'));
        }

        if ($extension->booking->status !== BookingStatus::Active) {
            throw ValidationException::withMessages([
                'extension' => __('messages.booking.extension_only_active'),
            ]);
        }
    }

    /**
     * @return array<int, array{status: string, actor: string|null, time: \Illuminate\Support\Carbon|null, note: string|null}>
     */
    public function timelineFor(Booking $booking): array    {
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
