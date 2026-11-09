<?php

namespace App\Enums;

enum BookingItemStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case PickedUp = 'picked_up';
    case Returned = 'returned';
    case Damaged = 'damaged';
    case Lost = 'lost';
}
