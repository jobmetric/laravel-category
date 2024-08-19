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

        return [
            'type' => 'required|string|in:' . implode(',', getCategoryType('key')),
            'parent_id' => [
                'nullable',
                'integer',
                new CategoryExistRule($type)
            ],
            'ordering' => 'numeric|nullable',
            'status' => 'boolean|nullable',

            'translation' => 'array',
            'translation.name' => [
                'string',
                new TranslationFieldExistRule(Category::class, 'name', parent_id: $parent_id),
            ],
            'translation.description' => 'string|nullable',
            'translation.meta_title' => 'string|nullable',
            'translation.meta_description' => 'string|nullable',
            'translation.meta_keywords' => 'string|nullable',
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
        $this->merge([
            'parent_id' => null,
            'ordering' => 0,
            'status' => $this->status ?? true,
        ]);
    }
}
