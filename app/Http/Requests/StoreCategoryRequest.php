<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('organization_admin');
    }

    public function rules(): array
    {
        $organizationId = $this->user()->organization_id;

        return [
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('organization_id', $organizationId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
