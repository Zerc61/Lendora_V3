<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReassignBookingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role?->slug, ['operator', 'organization_admin'], true);
    }

    public function rules(): array
    {
        return [
            'booking_item_id' => ['required', 'integer', Rule::exists('booking_items', 'id')],
            'asset_id' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where('organization_id', $this->user()->organization_id),
            ],
        ];
    }
}
