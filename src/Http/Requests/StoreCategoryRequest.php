<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Category\Rules\CategoryExistRule;
use JobMetric\Category\Rules\CheckSlugInTypeRule;

class StoreCategoryRequest extends FormRequest
{
    public array $data = [];

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
        if(empty($this->data)) {
            $type = $this->input('type');
        } else {
            $type = $this->data['type'] ?? null;
        }

        return [
            'slug' => [
                'string',
                'nullable',
                new CheckSlugInTypeRule($type)
            ],
            'parent_id' => [
                'integer',
                new CategoryExistRule($type)
            ],
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

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
