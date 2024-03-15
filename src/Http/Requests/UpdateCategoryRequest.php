<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Category\Rules\CategoryExistRule;
use JobMetric\Category\Rules\CheckSlugInTypeRule;

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
        if(is_null($this->category_id)) {
            $category_id = $this->route()->parameter('category')->id;
        } else {
            $category_id = $this->category_id;
        }

        if(empty($this->data)) {
            $type = $this->input('type');
        } else {
            $type = $this->type ?? null;
        }

        return [
            'slug' => [
                'string',
                'nullable',
                'sometimes',
                new CheckSlugInTypeRule($type, $category_id)
            ],
            'parent_id' => [
                'integer',
                'sometimes',
                new CategoryExistRule($type)
            ],
            'ordering' => 'integer|sometimes',
            'status' => 'boolean|sometimes',

            'translations' => 'array|sometimes',
            'translations.*.title' => 'string|required',
            'translations.*.body' => 'string|nullable',
            'translations.*.meta_title' => 'string|nullable',
            'translations.*.meta_description' => 'string|nullable',
            'translations.*.meta_keywords' => 'string|nullable',
        ];
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
}
