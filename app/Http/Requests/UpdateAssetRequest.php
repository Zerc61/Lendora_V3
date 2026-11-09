<?php

namespace App\Http\Requests;

use App\Enums\AssetCondition;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('asset'));
    }

    public function rules(): array
    {
        /** @var Asset $asset */
        $asset = $this->route('asset');
        $organizationId = $this->user()->organization_id;

        return [
            'code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('assets', 'code')->ignore($asset->id),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('organization_id', $organizationId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'brand' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'serial_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('assets', 'serial_number')->ignore($asset->id),
            ],
            'description' => ['nullable', 'string'],
            'condition_status' => ['nullable', Rule::enum(AssetCondition::class)],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'location' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:2048'],
        ];
    }
}
