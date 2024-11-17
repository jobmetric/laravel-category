<?php

namespace JobMetric\Taxonomy\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Rules\TaxonomyExistRule;
use JobMetric\Media\Http\Requests\MediaTypeObjectRequest;
use JobMetric\Metadata\Http\Requests\MetadataTypeObjectRequest;
use JobMetric\Translation\Http\Requests\TranslationTypeObjectRequest;
use JobMetric\Url\Http\Requests\UrlTypeObjectRequest;

class StoreTaxonomyRequest extends FormRequest
{
    use TranslationTypeObjectRequest, MetadataTypeObjectRequest, MediaTypeObjectRequest, UrlTypeObjectRequest;

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
            'type' => 'required|string|in:' . implode(',', getTaxonomyType('key')),
            'parent_id' => [
                'nullable',
                'integer',
                new TaxonomyExistRule($type)
            ],
            'ordering' => 'numeric|sometimes',
            'status' => 'boolean|sometimes',
        ];

        $taxonomyTypes = getTaxonomyType();

        checkTypeInTaxonomyTypes($type);
        $this->renderTranslationFiled($rules, $taxonomyTypes[$type], Taxonomy::class, 'name', parent_id: $parent_id, parent_where: ['type' => $type]);
        $this->renderMetadataFiled($rules, $taxonomyTypes[$type]);
        $this->renderMediaFiled($rules, $taxonomyTypes[$type]);
        if($taxonomyTypes[$type]['has_url']) {
            $this->renderUrlFiled($rules, Taxonomy::class, $type);
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

        if (!getTaxonomyTypeArg($type, 'hierarchical')) {
            $this->merge([
                'parent_id' => null,
            ]);
        }

        $this->merge([
            'ordering' => $this->ordering ?? 0,
            'status' => $this->status ?? true,
        ]);
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
            if (isset($taxonomyTypes['translation']['fields'])) {
                foreach ($taxonomyTypes['translation']['fields'] as $field_key => $field_value) {
                    $params['translation.' . $field_key] = trans($field_value['label']);
                }
            }
            if (isset($taxonomyTypes['translation']['seo']) && $taxonomyTypes['translation']['seo']) {
                $params['translation.meta_title'] = trans('translation::base.fields.meta_title.label');
                $params['translation.meta_description'] = trans('translation::base.fields.meta_description.label');
                $params['translation.meta_keywords'] = trans('translation::base.fields.meta_keywords.label');
            }
        }

        if (isset($taxonomyTypes['metadata'])) {
            foreach ($taxonomyTypes['metadata'] as $field_key => $field_value) {
                $params['metadata.' . $field_key] = trans($field_value['label']);
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
                $params['media.' . $media_collection] = trans('taxonomy::base.form.media.' . $media_collection . '.title');
            }
        }

        return $params;
    }
}
