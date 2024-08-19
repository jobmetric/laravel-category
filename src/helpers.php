<?php

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

        if ($mode === 'value') {
            return $categoryTypes->values()->map(function ($value) {
                return trans($value);
            })->toArray();
        }

        return $categoryTypes->map(function ($value) {
            return trans($value);
        })->toArray();
    }
}
