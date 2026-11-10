<?php

namespace App\Http\Requests;

use App\Enums\MaintenancePriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role?->slug, ['operator', 'organization_admin'], true);
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'asset_id' => [
                'required',
                Rule::exists('assets', 'id')->where('organization_id', $organizationId),
            ],
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->where('organization_id', $organizationId),
            ],
            'priority' => ['nullable', Rule::enum(MaintenancePriority::class)],
            'vendor' => ['nullable', 'string', 'max:120'],
            'due_at' => ['nullable', 'date'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }
}
