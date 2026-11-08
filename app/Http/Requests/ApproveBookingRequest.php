<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ApproveBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking instanceof Booking && $this->user()->can('approve', $booking);
    }

    public function rules(): array
    {
        return [];
    }
}
