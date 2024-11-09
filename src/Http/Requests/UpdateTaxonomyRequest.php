<?php

namespace JobMetric\Taxonomy\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Rules\TaxonomyExistRule;
use JobMetric\Language\Facades\Language;
use JobMetric\Media\Http\Requests\MediaTypeObjectRequest;
use JobMetric\Metadata\Http\Requests\MetadataTypeObjectRequest;
use JobMetric\Translation\Http\Requests\MultiTranslationTypeObjectRequest;
use JobMetric\Url\Http\Requests\UrlTypeObjectRequest;

class UpdateTaxonomyRequest extends FormRequest
{
    use MultiTranslationTypeObjectRequest, MetadataTypeObjectRequest, MediaTypeObjectRequest, UrlTypeObjectRequest;

    public string|null $type = null;
    public int|null $taxonomy_id = null;
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

        if (is_null($this->taxonomy_id)) {
            $taxonomy_id = $this->route()->parameter('jm_taxonomy')->id;
        } else {
            $taxonomy_id = $this->taxonomy_id;
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
                new TaxonomyExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',
        ];

        $taxonomyTypes = getTaxonomyType();

        $this->renderMultiTranslationFiled($rules, $taxonomyTypes[$type], Taxonomy::class, 'name', $taxonomy_id, $parent_id, ['type' => $type]);
        $this->renderMetadataFiled($rules, $taxonomyTypes[$type]);
        $this->renderMediaFiled($rules, $taxonomyTypes[$type]);
        if($taxonomyTypes[$type]['has_url']) {
            $this->renderUrlFiled($rules, Taxonomy::class, $type, $taxonomy_id);
        }

        if (!getTaxonomyTypeArg($type, 'hierarchical')) {
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
     * Set taxonomy id for validation
     *
     * @param int $taxonomy_id
     * @return static
     */
    public function setTaxonomyId(int $taxonomy_id): static
    {
        $this->taxonomy_id = $taxonomy_id;

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

        if (!getTaxonomyTypeArg($type, 'hierarchical')) {
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
            'parent_id' => trans('taxonomy::base.form.fields.parent.title'),
            'ordering' => trans('taxonomy::base.form.fields.ordering.title'),
            'status' => trans('package-core::base.components.boolean_status.label'),
            'translation.name' => trans('translation::base.fields.name.label'),
        ];

        $taxonomyTypes = getTaxonomyType(type: $type);

        if (isset($taxonomyTypes['translation'])) {
            $languages = Language::all();

            foreach ($languages as $language) {
                if (isset($taxonomyTypes['translation']['fields'])) {
                    foreach ($taxonomyTypes['translation']['fields'] as $field_key => $field_value) {
                        $params["translation.$language->locale.$field_key"] = trans($field_value['label']);
                    }
                }
                if (isset($taxonomyTypes['translation']['seo']) && $taxonomyTypes['translation']['seo']) {
                    $params["translation.$language->locale.meta_title"] = trans('translation::base.fields.meta_title.label');
                    $params["translation.$language->locale.meta_description"] = trans('translation::base.fields.meta_description.label');
                    $params["translation.$language->locale.meta_keywords"] = trans('translation::base.fields.meta_keywords.label');
                }
            }
        }

        if (isset($taxonomyTypes['metadata'])) {
            foreach ($taxonomyTypes['metadata'] as $field_key => $field_value) {
                $params["metadata.$field_key"] = trans($field_value['label']);
            }
        }

        if (isset($taxonomyTypes['has_url']) && $taxonomyTypes['has_url']) {
            $params['slug'] = trans('url::base.components.url_slug.title');
        }

        if (isset($taxonomyTypes['has_base_media']) && $taxonomyTypes['has_base_media']) {
            $params['media.base'] = trans('taxonomy::base.form.media.base.title');
        }

        if (isset($taxonomyTypes['media'])) {
            foreach ($taxonomyTypes['media'] as $media_collection => $media_item) {
                $params["media.$media_collection"] = trans("taxonomy::base.form.media.$media_collection.title");
            }
        }

        return $params;
    }
}
