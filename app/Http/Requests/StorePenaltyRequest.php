<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenaltyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role?->slug, ['operator', 'organization_admin'], true);
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'booking_id' => [
                'required',
                Rule::exists('bookings', 'id')->where('organization_id', $organizationId),
            ],
            'asset_id' => [
                'nullable',
                Rule::exists('assets', 'id')->where('organization_id', $organizationId),
            ],
            'borrower_id' => [
                'required',
                Rule::exists('users', 'id')->where('organization_id', $organizationId),
            ],
            'maintenance_id' => ['nullable', Rule::exists('maintenances', 'id')],
            'incident_at' => ['required', 'date', 'before_or_equal:now'],
            'inspector_name' => ['required', 'string', 'max:150'],
            'damage_description' => ['required', 'string', 'max:255'],
            'case_category' => ['nullable', 'string', 'max:60'],
            'chronology' => ['nullable', 'string', 'max:2000'],
            'payment_terms_days' => ['required', 'integer', 'min:1', 'max:60'],
            'subsidy_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'admin_fee' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.part_code' => ['nullable', 'string', 'max:60'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_cost' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $booking = Booking::with('items')->find($this->input('booking_id'));

            if ($booking === null) {
                return;
            }

            if ((int) $booking->borrower_id !== (int) $this->input('borrower_id')) {
                $validator->errors()->add('borrower_id', __('messages.bap.borrower_mismatch'));
            }

            if ($this->filled('asset_id')
                && ! $booking->items->contains('asset_id', (int) $this->input('asset_id'))) {
                $validator->errors()->add('asset_id', __('messages.bap.asset_mismatch'));
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->validated() + [
            'organization_id' => $this->user()->organization_id,
        ];
    }
}
