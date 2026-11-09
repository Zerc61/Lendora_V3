<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;

class BookingPolicy
{
    public function __construct(private readonly BookingService $bookings) {}

    public function viewAny(User $user): bool
    {
        return $user->role !== null;
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->organization_id !== $booking->organization_id) {
            return false;
        }

        if ($user->hasRole('borrower')) {
            return $booking->borrower_id === $user->id;
        }

        return in_array($user->role?->slug, ['operator', 'organization_admin', 'admin_prodi'], true);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('borrower');
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $booking->status === BookingStatus::Pending
            && $this->bookings->decisionBlockReason($user, $booking) === null;
    }

    public function reject(User $user, Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::Pending) {
            return false;
        }

        if (! in_array($user->role?->slug, ['operator', 'organization_admin'], true)) {
            return false;
        }

        return $this->bookings->decisionBlockReason($user, $booking) === null;
    }

    public function cancel(User $user, Booking $booking): bool
    {        if ($user->organization_id !== $booking->organization_id) {
            return false;
        }

        if ($booking->status === BookingStatus::Pending) {
            return $user->hasRole('borrower') && $booking->borrower_id === $user->id;
        }

        if ($booking->status === BookingStatus::Approved && $booking->pickup_at === null) {
            return in_array($user->role?->slug, ['operator', 'organization_admin'], true);
        }

        return false;
    }

    public function requestExtension(User $user, Booking $booking): bool
    {
        return $user->organization_id === $booking->organization_id
            && $booking->borrower_id === $user->id
            && $booking->status === BookingStatus::Active;
    }
}
