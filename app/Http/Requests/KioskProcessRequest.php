<?php

namespace App\Http\Requests;

use App\Enums\AssetCondition;
use App\Enums\HandoverType;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KioskProcessRequest extends FormRequest
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
            'type' => ['required', Rule::enum(HandoverType::class)],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*' => ['required', Rule::enum(AssetCondition::class)],
            'accessories' => ['nullable', 'array'],
            'accessories.*' => ['array'],
            'accessories.*.*' => ['string', 'max:100'],
            'item_notes' => ['nullable', 'array'],
            'item_notes.*' => ['nullable', 'string', 'max:255'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['boolean'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'borrower_signature' => ['nullable', 'string', 'max:3000000'],
            'operator_signature' => ['nullable', 'string', 'max:3000000'],
            'shift_label' => ['nullable', 'string', 'max:60'],
            'inspect' => ['nullable', 'boolean'],
            'inspection_notes' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function booking(): Booking
    {
        return Booking::query()
            ->with(['borrower', 'items.asset'])
            ->where('organization_id', $this->user()->organization_id)
            ->findOrFail($this->integer('booking_id'));
    }
}
