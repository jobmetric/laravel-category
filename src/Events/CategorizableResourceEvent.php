<?php

namespace JobMetric\Category\Events;

class CategorizableResourceEvent
{
    /**
     * The categorizable model instance.
     *
     * @var mixed
     */
    public mixed $categorizable;

    /**
     * The resource to be filled by the listener.
     *
     * @var mixed|null
     */
    public mixed $resource;

    /**
     * Create a new event instance.
     *
     * @param mixed $categorizable
     */
    public function __construct(mixed $categorizable)
    {
        $this->categorizable = $categorizable;
        $this->resource = null;
    }
}
