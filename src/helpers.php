<?php

use JobMetric\Category\Exceptions\CategoryTypeNotMatchException;

if (!function_exists('getCategoryType')) {
    /**
     * Get the category type
     *
     * @param string|null $mode
     *
     * @return array
     */
    function getCategoryType(string $mode = null): array
    {
        $categoryTypes = collect(app('categoryType'));

        if ($mode === 'key') {
            return $categoryTypes->keys()->toArray();
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
            'label' => $categoryTypes[$type]['label'] ?? null,
            'description' => $categoryTypes[$type]['description'] ?? null,
            'hierarchical' => $categoryTypes[$type]['hierarchical'] ?? false,
            'translation' => $categoryTypes[$type]['translation'] ?? [],
            'metadata' => $categoryTypes[$type]['metadata'] ?? [],
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
