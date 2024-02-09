<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => 'string|nullable',
            'parent_id' => 'integer',
            'type' => 'string',
            'ordering' => 'integer',
            'status' => 'boolean',

            'translations' => 'array',
            'translations.*.title' => 'string|required',
            'translations.*.body' => 'string|nullable',
            'translations.*.meta_title' => 'string|nullable',
            'translations.*.meta_description' => 'string|nullable',
            'translations.*.meta_keywords' => 'string|nullable',
        ];
    }
}
