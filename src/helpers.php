<?php

use JobMetric\Category\Exceptions\CategoryTypeNotMatchException;

if (!function_exists('getCategoryType')) {
    /**
     * Get the category type
     *
     * @param string|null $mode
     * @param string|null $type
     *
     * @return array
     */
    function getCategoryType(string $mode = null, string $type = null): array
    {
        $categoryTypes = collect(app('categoryType'));

        if ($mode === 'key') {
            return $categoryTypes->keys()->toArray();
        }

        if ($type) {
            return $categoryTypes[$type] ?? [];
        }

        return $categoryTypes->toArray();
    }
}

if (!function_exists('getCategoryTypeArg')) {
    /**
     * Get the category type argument
     *
     * @param string $type
     * @param string $arg
     *
     * @return mixed
     */
    function getCategoryTypeArg(string $type, string $arg = 'label'): mixed
    {
        $categoryTypes = getCategoryType();

        return match ($arg) {
            'label' => isset($categoryTypes[$type]['label']) ? trans($categoryTypes[$type]['label']) : null,
            'description' => isset($categoryTypes[$type]['description']) ? trans($categoryTypes[$type]['description']) : null,
            'hierarchical' => $categoryTypes[$type]['hierarchical'] ?? false,
            'translation' => $categoryTypes[$type]['translation'] ?? [],
            'metadata' => $categoryTypes[$type]['metadata'] ?? [],
            'has_url' => $categoryTypes[$type]['has_url'] ?? false,
            'has_base_media' => $categoryTypes[$type]['has_base_media'] ?? false,
            'media' => $categoryTypes[$type]['media'] ?? [],
            default => null,
        };
    }
}

if (!function_exists('checkTypeInCategoryTypes')) {
    /**
     * Check type in category type
     *
     * @param string $type
     *
     * @return void
     * @throws Throwable
     */
    function checkTypeInCategoryTypes(string $type): void
    {
        $categoryTypes = getCategoryType();

        if (!array_key_exists($type, $categoryTypes)) {
            throw new CategoryTypeNotMatchException($type);
        }
    }
}
