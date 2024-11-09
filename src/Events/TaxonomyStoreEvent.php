<?php

namespace JobMetric\Taxonomy\Events;

use JobMetric\Taxonomy\Models\Taxonomy;

class TaxonomyStoreEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Taxonomy $taxonomy,
        public readonly array    $data,
        public readonly bool     $hierarchical,
    )
    {
    }
}
