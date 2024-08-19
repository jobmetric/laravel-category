<?php

namespace JobMetric\Category\Events;

use JobMetric\Category\Models\Category;

class CategoryDeleteEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Category $category,
    )
    {
    }
}
