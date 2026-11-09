<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('organization_admin');
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:150'],
            'organization_address' => ['nullable', 'string', 'max:255'],
            'organization_phone' => ['nullable', 'string', 'max:20'],
            'organization_email' => ['nullable', 'email', 'max:150'],
            'max_loan_days' => ['required', 'integer', 'min:1', 'max:90'],
            'operator_approval_limit' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'return_reminder_enabled' => ['nullable', 'boolean'],
            'borrower_announcement' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
