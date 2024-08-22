<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Category\Models\Category;
use JobMetric\Category\Rules\CategoryExistRule;
use JobMetric\Category\Rules\CheckSlugInTypeRule;
use JobMetric\Translation\Rules\TranslationFieldExistRule;

class UpdateCategoryRequest extends FormRequest
{
    public string|null $type = null;
    public int|null $category_id = null;
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        if(empty($this->data)) {
            $type = $this->input('type');
        } else {
            $type = $this->type ?? null;
        }

        if(is_null($this->category_id)) {
            $category_id = $this->route()->parameter('category')->id;
        } else {
            $category_id = $this->category_id;
        }

        if (is_null($this->parent_id)) {
            $parent_id = $this->input('parent_id');
        } else {
            $parent_id = $this->parent_id;
        }

        $categoryTypes = getCategoryType();
        $hierarchical = $categoryTypes[$type]['hierarchical'];

        $rules = [
            'parent_id' => [
                'nullable',
                'integer',
                'sometimes',
                new CategoryExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',

            'translation' => 'array|sometimes',
            'translation.name' => [
                'string',
                new TranslationFieldExistRule(Category::class, 'name', object_id: $category_id, parent_id: $parent_id),
            ],
            'translation.description' => 'string|nullable|sometimes',
            'translation.meta_title' => 'string|nullable|sometimes',
            'translation.meta_description' => 'string|nullable|sometimes',
            'translation.meta_keywords' => 'string|nullable|sometimes',
        ];

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
     * Set category id for validation
     *
     * @param int $category_id
     * @return static
     */
    public function setCategoryId(int $category_id): static
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Set data for validation
     *
     * @param array $data
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;

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
    }
}
