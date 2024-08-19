<?php

namespace JobMetric\Category\Events;

class CategoryTypeEvent
{
    /**
     * The category type to be filled by the listener.
     *
     * @var array
     */
    public array $categoryType = [];

    /**
     * Add a type.
     *
     * @param array $type Example: ['product' => 'base.category_type.product']
     *
     * @return static
     */
    public function AddType(array $type): static
    {
        if (!in_array($type, $this->categoryType)) {
            $this->categoryType = array_merge($this->categoryType, $type);
        }

        return $this;
    }
}
