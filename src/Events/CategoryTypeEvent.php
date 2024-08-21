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
     * @param string $type
     * @param string|null $trans_key
     * @param bool $hierarchical
     *
     * @return static
     */
    public function AddType(string $type, string $trans_key = null, bool $hierarchical = true): static
    {
        if (!array_key_exists($type, $this->categoryType)) {
            $this->categoryType = array_merge($this->categoryType, [
                $type => [
                    'trans_key' => $trans_key,
                    'hierarchical' => $hierarchical,
                ],
            ]);
        }

        return $this;
    }
}
