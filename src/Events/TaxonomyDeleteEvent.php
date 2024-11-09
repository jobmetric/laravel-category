<?php

namespace JobMetric\Taxonomy\Events;

use JobMetric\Taxonomy\Models\Taxonomy;

class TaxonomyDeleteEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Taxonomy $taxonomy,
    )
    {
    }
}
