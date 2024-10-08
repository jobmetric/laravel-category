<?php

namespace JobMetric\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Category\Models\Category;
use JobMetric\Category\Models\CategoryPath;
use JobMetric\Category\Models\CategoryRelation;
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
 * @property CategoryRelation[] categoryRelations
 * @property CategoryPath[] paths
 * @property Category[] children
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        global $translationLocale;

        $categoryTypes = getCategoryType();
        $hierarchical = $categoryTypes[$this->type]['hierarchical'];

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

            'categoryRelations' => $this->whenLoaded('categoryRelations', function () {
                return CategoryRelationResource::collection($this->categoryRelations);
            }),

            'paths' => $this->whenLoaded('paths', function () {
                return CategoryPathResource::collection($this->paths);
            }),

            'children_count' => $this->whenLoaded('children', function () {
                return count($this->children);
            }),
        ];
    }
}
