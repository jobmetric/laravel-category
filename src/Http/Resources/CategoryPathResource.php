<?php

namespace JobMetric\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Category\Models\Category;

/**
 * @property string type
 * @property int category_id
 * @property int path_id
 * @property int level
 *
 * @property Category category
 * @property Category path
 */
class CategoryPathResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'category_id' => $this->category_id,
            'path_id' => $this->path_id,
            'level' => $this->level,

            'category' => $this->whenLoaded('category', function () {
                return new CategoryResource($this->category);
            }),

            'path' => $this->whenLoaded('path', function () {
                return new CategoryResource($this->category);
            }),
        ];
    }
}
