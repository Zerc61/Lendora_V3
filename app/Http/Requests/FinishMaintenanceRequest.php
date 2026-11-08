<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinishMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $maintenance = $this->route('maintenance');

        return $this->user()->hasRole('technician')
            && $maintenance?->assigned_to === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'result_notes' => ['nullable', 'string', 'max:2000'],
            'cost' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'condition_good' => ['nullable', 'boolean'],
        ];
    }
}
