<?php

namespace App\Policies;

use App\Enums\BookingExtensionStatus;
use App\Models\BookingExtension;
use App\Models\User;

class BookingExtensionPolicy
{
    public function cancel(User $user, BookingExtension $extension): bool
    {
        return $extension->requested_by === $user->id
            && $extension->status === BookingExtensionStatus::Pending;
    }
}
