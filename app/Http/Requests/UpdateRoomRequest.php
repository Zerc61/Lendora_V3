<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin_prodi');
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;
        $room = $this->route('room');

        return [
            'code' => [
                'required', 'string', 'max:32',
                Rule::unique('rooms', 'code')
                    ->where('organization_id', $organizationId)
                    ->ignore($room instanceof Room ? $room->id : null),
            ],
            'name' => ['required', 'string', 'max:120'],
            'capacity' => ['nullable', 'integer', 'between:1,500'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => __('messages.practicum.room_code_unique'),
        ];
    }
}
