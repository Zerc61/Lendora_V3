<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('organization_admin');
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where('organization_id', $this->user()->organization_id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
