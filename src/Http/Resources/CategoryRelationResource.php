<?php

namespace JobMetric\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JobMetric\Category\Models\Category;

/**
 * @property mixed category_id
 * @property mixed categorizable_id
 * @property mixed categorizable_type
 * @property mixed collection
 * @property mixed created_at
 *
 * @property Category category
 * @property mixed categorizable
 * @property mixed categorizable_resource
 */
class CategoryRelationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->category_id,
            'categorizable_id' => $this->categorizable_id,
            'categorizable_type' => $this->categorizable_type,
            'collection' => $this->collection,
            'created_at' => $this->created_at,

            'category' => $this->whenLoaded('category', function () {
                return new CategoryResource($this->category);
            }),

            'categorizable' => $this?->categorizable_resource
        ];
    }
}
