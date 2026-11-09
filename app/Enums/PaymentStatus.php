<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case WaitingVerification = 'waiting_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
