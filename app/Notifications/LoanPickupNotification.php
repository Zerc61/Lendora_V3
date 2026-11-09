<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class LoanPickupNotification extends Notification
{
    public function __construct(public readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        return $notifiable->wantsNotification('loan_pickup') ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'code' => $this->booking->code,
            'due_at' => $this->booking->due_at?->toIso8601String(),
            'message' => __('messages.booking.notification_pickup', [
                'code' => $this->booking->code,
                'due' => $this->booking->due_at?->locale('id')->translatedFormat('d M Y H:i') ?? '-',
            ]),
        ];
    }
}
