<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Category\Models\Category;
use JobMetric\Category\Rules\CategoryExistRule;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

class StoreCategoryRequest extends FormRequest
{
    public ?string $type = null;
    public ?int $parent_id = null;

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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        if (is_null($this->type)) {
            $type = $this->input('type');
        } else {
            $type = $this->type;
        }

        if (is_null($this->parent_id)) {
            $parent_id = $this->input('parent_id');
        } else {
            $parent_id = $this->parent_id;
        }

        $categoryTypes = getCategoryType();
        $hierarchical = $categoryTypes[$type]['hierarchical'];

        $rules = [
            'type' => 'required|string|in:' . implode(',', getCategoryType('key')),
            'parent_id' => [
                'nullable',
                'integer',
                new CategoryExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',

            'translation' => 'array',
            'translation.name' => [
                'string',
                new TranslationFieldExistRule(Category::class, 'name', parent_id: $parent_id),
            ],
            'translation.description' => 'string|nullable|sometimes',
            'translation.meta_title' => 'string|nullable|sometimes',
            'translation.meta_description' => 'string|nullable|sometimes',
            'translation.meta_keywords' => 'string|nullable|sometimes',
        ];

        $getCategoryTypes = getCategoryType();

        if (isset($getCategoryTypes[$type]['metadata'])) {
            $rules['metadata'] = 'array|sometimes';
            foreach ($getCategoryTypes[$type]['metadata'] as $key => $value) {
                $rules['metadata.' . $key] = $value['validation'] ?? 'string|nullable|sometimes';
            }
        }

        if (!$hierarchical) {
            unset($rules['parent_id']);
        }

        return $rules;
    }

    /**
     * Set type for validation
     *
     * @param string $type
     * @return static
     */
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set parent_id for validation
     *
     * @param int|null $parent_id
     * @return static
     */
    public function setParentId(int $parent_id = null): static
    {
        $this->parent_id = $parent_id;

        return $this;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $type = $this->type ?? $this->input('type');
        $categoryTypes = getCategoryType();
        $hierarchical = $categoryTypes[$type]['hierarchical'];

        if (!$hierarchical) {
            $this->merge([
                'parent_id' => null,
            ]);
        }

        $this->merge([
            'ordering' => $this->ordering ?? 0,
            'status' => $this->status ?? true,
        ]);
    }
}
