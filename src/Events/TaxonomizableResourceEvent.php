<?php

namespace JobMetric\Taxonomy\Events;

class TaxonomizableResourceEvent
{
    /**
     * The taxonomizable model instance.
     *
     * @var mixed
     */
    public mixed $taxonomizable;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $taxonomizable
     */
    public function __construct(mixed $taxonomizable)
    {
        $this->taxonomizable = $taxonomizable;
        $this->resource = null;
    }
}
