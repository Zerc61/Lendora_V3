<?php

namespace App\Http\Requests;

use App\Enums\AssetCondition;
use App\Enums\HandoverType;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking instanceof Booking
            && $this->user()->can('view', $booking)
            && in_array($this->user()->role?->slug, ['operator', 'organization_admin'], true);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(HandoverType::class)],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*' => ['required', Rule::enum(AssetCondition::class)],
            'accessories' => ['nullable', 'array'],
            'accessories.*' => ['array'],
            'accessories.*.*' => ['string', 'max:100'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['boolean'],
            'checklists' => ['nullable', 'array'],
            'checklists.*' => ['array'],
            'checklists.*.*' => ['boolean'],
            'item_notes' => ['nullable', 'array'],
            'item_notes.*' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'borrower_signature' => ['nullable', 'string', 'max:3000000'],
            'operator_signature' => ['nullable', 'string', 'max:3000000'],
            'shift_label' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
