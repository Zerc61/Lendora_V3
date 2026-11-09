<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case VaBni = 'va_bni';
    case Qris = 'qris';
    case Sparepart = 'sparepart';
    case Installment = 'installment';
}
