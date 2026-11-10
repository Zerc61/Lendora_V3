<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingAlternativeNotification extends Notification
{
    /**
     * @param  array<int, array{booking_item_id: int, asset_id: int, code: string, name: string}>  $alternatives
     */
    public function __construct(
        public readonly Booking $booking,
        public readonly array $alternatives,
    ) {}

    public function via(object $notifiable): array
    {
        return $notifiable->wantsNotification('booking_decision') ? ['database'] : [];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'code' => $this->booking->code,
            'alternatives' => $this->alternatives,
            'message' => __('messages.booking.notification_alternative', ['code' => $this->booking->code]),
        ];
    }
}
