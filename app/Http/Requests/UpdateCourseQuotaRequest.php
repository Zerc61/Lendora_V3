<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin_prodi');
    }

    public function rules(): array
    {
        return [
            'quota_hours' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
