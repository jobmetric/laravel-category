<?php

namespace JobMetric\Taxonomy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Metadata\Http\Resources\MetadataResource;
use JobMetric\Metadata\Models\Meta;
use JobMetric\Taxonomy\Models\Taxonomy;
use JobMetric\Taxonomy\Models\TaxonomyPath;
use JobMetric\Taxonomy\Models\TaxonomyRelation;
use JobMetric\Translation\Models\Translation;

/**
 * @property mixed id
 * @property mixed type
 * @property mixed name
 * @property mixed parent_id
 * @property mixed ordering
 * @property mixed status
 * @property mixed created_at
 * @property mixed updated_at
 *
 * @property Translation[] translations
 * @property TaxonomyRelation[] taxonomyRelations
 * @property Meta[] metas
 * @property TaxonomyPath[] paths
 * @property Taxonomy[] children
 */
class TaxonomyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        global $translationLocale;

        $taxonomyTypes = getTaxonomyType();
        $hierarchical = $taxonomyTypes[$this->type]['hierarchical'];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'hierarchical' => $hierarchical,
            'name' => $this->whenHas('name', $this->name),
            'name_multiple' => $this->whenHas('name_multiple', $this->name_multiple),
            'parent_id' => $this->when($hierarchical, $this->parent_id),
            'ordering' => $this->ordering,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'translations' => translationResourceData($this->translations, $translationLocale),

            'taxonomyRelations' => $this->whenLoaded('taxonomyRelations', function () {
                return TaxonomyRelationResource::collection($this->taxonomyRelations);
            }),

            'metas' => $this->whenLoaded('metas', function () {
                return MetadataResource::collection($this->metas);
            }),

            'paths' => $this->whenLoaded('paths', function () {
                return TaxonomyPathResource::collection($this->paths);
            }),

            'children_count' => $this->whenLoaded('children', function () {
                return count($this->children);
            }),
        ];
    }
}
