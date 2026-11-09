<?php

namespace App\Http\Requests;

use App\Enums\InspectionResult;
use App\Models\BookingItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role?->slug, ['operator', 'organization_admin', 'technician'], true);
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'asset_id' => [
                'required',
                Rule::exists('assets', 'id')->where('organization_id', $organizationId),
            ],
            'booking_item_id' => [
                'nullable',
                Rule::exists('booking_items', 'id'),
            ],
            'result' => ['required', Rule::enum(InspectionResult::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $itemId = $this->input('booking_item_id');

            if ($itemId === null) {
                return;
            }

            $item = BookingItem::find($itemId);

            if ($item !== null && (int) $item->asset_id !== (int) $this->input('asset_id')) {
                $validator->errors()->add('booking_item_id', __('messages.inspection.item_mismatch'));
            }
        });
    }
}
