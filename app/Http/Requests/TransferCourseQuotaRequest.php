<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferCourseQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin_prodi');
    }

    public function rules(): array
    {
        return [
            'from_course_id' => ['required', 'integer'],
            'to_course_id' => ['required', 'integer', 'different:from_course_id'],
            'hours' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_course_id.different' => __('messages.course_quota.transfer_same'),
        ];
    }
}
