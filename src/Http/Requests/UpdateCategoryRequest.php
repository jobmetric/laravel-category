<?php

namespace JobMetric\Category\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Category\Models\Category;
use JobMetric\Category\Rules\CategoryExistRule;
use JobMetric\Language\Facades\Language;
use JobMetric\Media\Http\Requests\MediaTypeObjectRequest;
use JobMetric\Metadata\Http\Requests\MetadataTypeObjectRequest;
use JobMetric\Translation\Http\Requests\MultiTranslationTypeObjectRequest;
use JobMetric\Url\Http\Requests\UrlTypeObjectRequest;

class UpdateCategoryRequest extends FormRequest
{
    use MultiTranslationTypeObjectRequest, MetadataTypeObjectRequest, MediaTypeObjectRequest, UrlTypeObjectRequest;

    public string|null $type = null;
    public int|null $category_id = null;
    public int|null $parent_id = null;
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
        if (empty($this->data)) {
            $type = $this->input('type');
        } else {
            $type = $this->type ?? null;
        }

        if (is_null($this->category_id)) {
            $category_id = $this->route()->parameter('jm_category')->id;
        } else {
            $category_id = $this->category_id;
        }

        if (is_null($this->parent_id)) {
            $parent_id = $this->input('parent_id');
        } else {
            $parent_id = $this->parent_id;
        }

        $rules = [
            'parent_id' => [
                'nullable',
                'integer',
                'sometimes',
                new CategoryExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',
        ];

        $categoryTypes = getCategoryType();

        $this->renderMultiTranslationFiled($rules, $categoryTypes[$type], Category::class, 'name', $category_id, $parent_id, ['type' => $type]);
        $this->renderMetadataFiled($rules, $categoryTypes[$type]);
        $this->renderMediaFiled($rules, $categoryTypes[$type]);
        if($categoryTypes[$type]['has_url']) {
            $this->renderUrlFiled($rules, Category::class, $type, $category_id);
        }

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

        if (!getCategoryTypeArg($type, 'hierarchical')) {
            $this->merge([
                'parent_id' => null,
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $type = $this->type ?? $this->input('type');

        $params = [
            'parent_id' => trans('category::base.form.fields.parent.title'),
            'ordering' => trans('category::base.form.fields.ordering.title'),
            'status' => trans('package-core::base.components.boolean_status.label'),
            'translation.name' => trans('translation::base.fields.name.label'),
        ];

        $categoryTypes = getCategoryType(type: $type);

        if (isset($categoryTypes['translation'])) {
            $languages = Language::all();

            foreach ($languages as $language) {
                if (isset($categoryTypes['translation']['fields'])) {
                    foreach ($categoryTypes['translation']['fields'] as $field_key => $field_value) {
                        $params["translation.$language->locale.$field_key"] = trans($field_value['label']);
                    }
                }
                if (isset($categoryTypes['translation']['seo']) && $categoryTypes['translation']['seo']) {
                    $params["translation.$language->locale.meta_title"] = trans('translation::base.fields.meta_title.label');
                    $params["translation.$language->locale.meta_description"] = trans('translation::base.fields.meta_description.label');
                    $params["translation.$language->locale.meta_keywords"] = trans('translation::base.fields.meta_keywords.label');
                }
            }
        }

        if (isset($categoryTypes['metadata'])) {
            foreach ($categoryTypes['metadata'] as $field_key => $field_value) {
                $params["metadata.$field_key"] = trans($field_value['label']);
            }
        }

        if (isset($categoryTypes['has_url']) && $categoryTypes['has_url']) {
            $params['slug'] = trans('url::base.components.url_slug.title');
        }

        if (isset($categoryTypes['has_base_media']) && $categoryTypes['has_base_media']) {
            $params['media.base'] = trans('category::base.form.media.base.title');
        }

        if (isset($categoryTypes['media'])) {
            foreach ($categoryTypes['media'] as $media_collection => $media_item) {
                $params["media.$media_collection"] = trans("category::base.form.media.$media_collection.title");
            }
        }

        return $params;
    }
}
