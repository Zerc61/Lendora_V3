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
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
