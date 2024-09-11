<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Category\Models\Category;
use JobMetric\Category\Rules\CategoryExistRule;
use JobMetric\Media\Http\Requests\MediaTypeObjectRequest;
use JobMetric\Metadata\Http\Requests\MetadataTypeObjectRequest;
use JobMetric\Translation\Http\Requests\TranslationTypeObjectRequest;

class StoreCategoryRequest extends FormRequest
{
    use TranslationTypeObjectRequest, MetadataTypeObjectRequest, MediaTypeObjectRequest;

    public string|null $type = null;
    public int|null $parent_id = null;

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

        $rules = [
            'type' => 'required|string|in:' . implode(',', getCategoryType('key')),
            'parent_id' => [
                'nullable',
                'integer',
                new CategoryExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',
        ];

        $categoryTypes = getCategoryType();

        $this->renderTranslationFiled($rules, $categoryTypes[$type], Category::class, 'name', parent_id: $parent_id, parent_where: ['type' => $type]);
        $this->renderMetadataFiled($rules, $categoryTypes[$type]);
        $this->renderMediaFiled($rules, $categoryTypes[$type]);

        if (!getCategoryTypeArg($type, 'hierarchical')) {
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

        if (!getCategoryTypeArg($type, 'hierarchical')) {
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
