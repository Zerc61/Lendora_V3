<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin_prodi');
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'code' => [
                'required', 'string', 'max:32',
                Rule::unique('courses', 'code')->where('organization_id', $organizationId),
            ],
            'name' => ['required', 'string', 'max:120'],
            'lecturer_name' => ['nullable', 'string', 'max:120'],
            'semester' => ['required', 'integer', 'between:1,14'],
            'credits' => ['required', 'integer', 'between:1,24'],
            'quota_hours' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => __('messages.course_quota.code_unique'),
        ];
    }
}
