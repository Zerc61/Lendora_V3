<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalkInBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role?->slug, ['operator', 'organization_admin'], true);
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'borrower_id' => [
                'required',
                Rule::exists('users', 'id')->where('organization_id', $organizationId),
            ],
            'asset_ids' => ['required', 'array', 'min:1'],
            'asset_ids.*' => [
                'integer',
                Rule::exists('assets', 'id')->where('organization_id', $organizationId),
            ],
            'requested_start_date' => ['required', 'date', 'after_or_equal:today'],
            'requested_end_date' => ['required', 'date', 'after_or_equal:requested_start_date'],
            'purpose' => ['required', 'string', 'min:5', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->validated();
    }
}
