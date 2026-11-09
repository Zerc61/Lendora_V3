<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('organization_admin');
    }

    public function rules(): array
    {
        /** @var Category $category */
        $category = $this->route('category');
        $organizationId = $this->user()->organization_id;

        return [
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('organization_id', $organizationId),
                Rule::notIn([$category->id]),
            ],
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
