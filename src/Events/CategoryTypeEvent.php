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
     * @param array $params
     *
     * @return static
     */
    public function addType(array $params): static
    {
        $type = $params['type'];
        $trans_key = $params['trans_key'] ?? null;
        $hierarchical = $params['hierarchical'] ?? true;
        $metadata = $params['metadata'] ?? [];

        if (!array_key_exists($type, $this->categoryType)) {
            $this->categoryType = array_merge($this->categoryType, [
                $type => [
                    'trans_key' => $trans_key,
                    'hierarchical' => $hierarchical,
                    'metadata' => $metadata,
                ],
            ]);
        }

        return $this;
    }
}
