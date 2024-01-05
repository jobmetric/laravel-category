<?php

namespace JobMetric\Category\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Category\Category
 */
class Category extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Category';
    }
}
