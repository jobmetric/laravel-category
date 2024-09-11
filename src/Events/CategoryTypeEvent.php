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
        $label = $params['args']['label'] ?? null;
        $description = $params['args']['description'] ?? null;
        $hierarchical = $params['args']['hierarchical'] ?? false;
        $translation = $params['args']['translation'] ?? [];
        $metadata = $params['args']['metadata'] ?? [];
        $has_base_media = $params['args']['has_base_media'] ?? false;
        $media = $params['args']['media'] ?? [];

        if (!array_key_exists($type, $this->categoryType)) {
            $this->categoryType = array_merge($this->categoryType, [
                $type => [
                    'label' => $label,
                    'description' => $description,
                    'hierarchical' => $hierarchical,
                    'translation' => $translation,
                    'metadata' => $metadata,
                    'has_base_media' => $has_base_media,
                    'media' => $media,
                ],
            ]);
        }

        return $this;
    }
}
