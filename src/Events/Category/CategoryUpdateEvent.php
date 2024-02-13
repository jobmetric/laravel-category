<?php

namespace JobMetric\Category\Events\Category;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JobMetric\Category\Models\Category;

class CategoryUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
