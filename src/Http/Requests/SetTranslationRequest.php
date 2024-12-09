<?php

namespace JobMetric\Taxonomy\Http\Requests;

use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Rules\TaxonomyExistRule;
use JobMetric\Media\Http\Requests\MediaTypeObjectRequest;
use JobMetric\Metadata\Http\Requests\MetadataTypeObjectRequest;
use JobMetric\Translation\Http\Requests\TranslationTypeObjectRequest;
use JobMetric\Url\Http\Requests\UrlTypeObjectRequest;
use InvalidArgumentException;

class SetTranslationRequest extends FormRequest
{
    use TranslationTypeObjectRequest;

    public string|null $type = null;

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
     * @throws Exception
     */
    public function rules(): array
    {
        $form_data = request()->all();
        $type = $this->route()->parameters()['type'];

        $locale = $form_data['locale'] ?? null;
        $id = $form_data['translatable_id'] ?? null;

        if (is_null($locale)) {
            throw new InvalidArgumentException('Locale is required', 400);
        }

        if (is_null($id)) {
            throw new InvalidArgumentException('Translatable ID is required', 400);
        }

        /**
         * @var Taxonomy $taxonomy
         */
        $taxonomy = Taxonomy::query()->findOrFail($id);

        $rules = [
            'locale' => ['required', 'string'],
            'translatable_id' => ['required', 'integer', new TaxonomyExistRule($type)],
        ];

        $taxonomyTypes = getTaxonomyType();
        $this->renderTranslationFiled($rules, $taxonomyTypes[$type], Taxonomy::class, 'name', $locale, $id, $taxonomy->parent_id, ['type' => $type]);

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $type = $this->route()->parameters()['type'];

        $params = [
            'translation.name' => trans('translation::base.components.translation_card.fields.name.label'),
        ];

        $taxonomyTypes = getTaxonomyType(type: $type);

        if (isset($taxonomyTypes['translation'])) {
            if (isset($taxonomyTypes['translation']['fields'])) {
                foreach ($taxonomyTypes['translation']['fields'] as $field_key => $field_value) {
                    $params['translation.' . $field_key] = trans($field_value['label']);
                }
            }
            if (isset($taxonomyTypes['translation']['seo']) && $taxonomyTypes['translation']['seo']) {
                $params['translation.meta_title'] = trans('translation::base.components.translation_card.fields.meta_title.label');
                $params['translation.meta_description'] = trans('translation::base.components.translation_card.fields.meta_description.label');
                $params['translation.meta_keywords'] = trans('translation::base.components.translation_card.fields.meta_keywords.label');
            }
        }

        return $params;
    }
}
