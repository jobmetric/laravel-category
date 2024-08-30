<?php

namespace JobMetric\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Category\Models\CategoryRelation;

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
 * @property mixed translations
 * @property CategoryRelation[] categoryRelations
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
            'parent_id' => $this->mergeWhen($hierarchical, $this->parent_id),
            'ordering' => $this->ordering,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'translations' => translationResourceData($this->translations, $translationLocale),

            'categoryRelations' => $this->whenLoaded('categoryRelations', function () {
                return CategoryRelationResource::collection($this->categoryRelations);
            }),
        ];
    }
}
