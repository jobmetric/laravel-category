<?php

namespace JobMetric\Taxonomy\Events;

use JobMetric\Taxonomy\Models\Taxonomy;

class TaxonomyUpdateEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Taxonomy $taxonomy,
        public readonly array    $data,
        public readonly bool     $change_parent_id
    )
    {
    }
}
