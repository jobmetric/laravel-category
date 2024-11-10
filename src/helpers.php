<?php

use JobMetric\Taxonomy\Exceptions\TaxonomyTypeNotMatchException;

if (!function_exists('getTaxonomyType')) {
    /**
     * Get the taxonomy type
     *
     * @param string|null $mode
     * @param string|null $type
     *
     * @return array
     */
    function getTaxonomyType(string $mode = null, string $type = null): array
    {
        $taxonomyTypes = collect(app('taxonomyType'));

        if ($mode === 'key') {
            return $taxonomyTypes->keys()->toArray();
        }

        if ($type) {
            return $taxonomyTypes[$type] ?? [];
        }

        return $taxonomyTypes->toArray();
    }
}

if (!function_exists('getTaxonomyTypeArg')) {
    /**
     * Get the taxonomy type argument
     *
     * @param string $type
     * @param string $arg
     *
     * @return mixed
     */
    function getTaxonomyTypeArg(string $type, string $arg = 'label'): mixed
    {
        $taxonomyTypes = getTaxonomyType();

        return match ($arg) {
            'label' => isset($taxonomyTypes[$type]['label']) ? trans($taxonomyTypes[$type]['label']) : null,
            'description' => isset($taxonomyTypes[$type]['description']) ? trans($taxonomyTypes[$type]['description']) : null,
            'hierarchical' => $taxonomyTypes[$type]['hierarchical'] ?? false,
            'translation' => $taxonomyTypes[$type]['translation'] ?? [],
            'metadata' => $taxonomyTypes[$type]['metadata'] ?? [],
            'has_url' => $taxonomyTypes[$type]['has_url'] ?? false,
            'has_base_media' => $taxonomyTypes[$type]['has_base_media'] ?? false,
            'media' => $taxonomyTypes[$type]['media'] ?? [],
            'configuration' => $taxonomyTypes[$type]['configuration'] ?? [],
            default => null,
        };
    }
}

if (!function_exists('checkTypeInTaxonomyTypes')) {
    /**
     * Check type in taxonomy type
     *
     * @param string $type
     *
     * @return void
     * @throws Throwable
     */
    function checkTypeInTaxonomyTypes(string $type): void
    {
        $taxonomyTypes = getTaxonomyType();

        if (!array_key_exists($type, $taxonomyTypes)) {
            throw new TaxonomyTypeNotMatchException($type);
        }
    }
}
