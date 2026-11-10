<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePracticumScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin_prodi');
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer'],
            'room_id' => ['required', 'integer'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $organizationId = $this->user()->organization_id;

                if (! Course::query()
                    ->where('organization_id', $organizationId)
                    ->where('is_active', true)
                    ->whereKey($this->input('course_id'))
                    ->exists()) {
                    $validator->errors()->add('course_id', __('messages.practicum.course_invalid'));
                }

                if (! Room::query()
                    ->where('organization_id', $organizationId)
                    ->where('is_active', true)
                    ->whereKey($this->input('room_id'))
                    ->exists()) {
                    $validator->errors()->add('room_id', __('messages.practicum.room_invalid'));
                }
            },
        ];
    }
}
