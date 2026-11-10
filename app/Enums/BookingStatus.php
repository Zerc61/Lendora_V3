<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Active = 'active';
    case Returned = 'returned';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
