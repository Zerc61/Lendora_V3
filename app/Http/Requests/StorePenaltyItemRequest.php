<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenaltyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role?->slug, ['operator', 'organization_admin'], true);
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:200'],
            'part_code' => ['nullable', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'unit_cost' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }
}
