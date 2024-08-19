<?php

namespace JobMetric\Category\Events;

use JobMetric\Category\Models\Category;

class CategoryStoreEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Category $category,
        public readonly array    $data
    )
    {
    }
}
