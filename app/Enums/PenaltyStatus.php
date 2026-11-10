<?php

namespace App\Enums;

enum PenaltyStatus: string
{
    case WaitingPayment = 'waiting_payment';
    case Partial = 'partial';
    case InstallmentRequested = 'installment_requested';
    case Paid = 'paid';
    case Waived = 'waived';
    case Overdue = 'overdue';
}
