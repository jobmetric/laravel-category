<?php

namespace JobMetric\Category\Events;

use JobMetric\Category\Models\Category;

class CategoryUpdateEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Category $category,
        public readonly array    $data,
        public readonly bool     $change_parent_id
    )
    {
    }
}
