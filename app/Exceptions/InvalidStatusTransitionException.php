<?php

namespace App\Exceptions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidStatusTransitionException extends HttpException
{
    public static function for(Booking $booking, BookingStatus $to): self
    {
        return new self(
            422,
            __('messages.booking.invalid_transition', [
                'code' => $booking->code,
                'from' => $booking->status->value,
                'to' => $to->value,
            ]),
        );
    }
}
