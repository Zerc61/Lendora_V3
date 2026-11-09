<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingDecisionNotification extends Notification
{
    public function __construct(
        public readonly Booking $booking,
        public readonly bool $approved,
        public readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'code' => $this->booking->code,
            'approved' => $this->approved,
            'reason' => $this->reason,
            'message' => $this->approved
                ? __('messages.booking.notification_approved', ['code' => $this->booking->code])
                : __('messages.booking.notification_rejected', [
                    'code' => $this->booking->code,
                    'reason' => $this->reason,
                ]),
        ];
    }
}
