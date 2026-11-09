<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBorrowerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('borrower');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'notifications' => ['nullable', 'array'],
            'notifications.*' => ['boolean'],
        ];
    }
}
