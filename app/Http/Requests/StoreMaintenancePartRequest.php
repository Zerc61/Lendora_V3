<?php

namespace App\Http\Requests;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenancePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        $maintenance = $this->route('maintenance');

        return $maintenance instanceof Maintenance
            && in_array($this->user()->role?->slug, ['operator', 'organization_admin', 'technician'], true)
            && $maintenance->asset?->organization_id === $this->user()->organization_id;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'part_code' => ['nullable', 'string', 'max:60'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'unit_cost' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }
}
