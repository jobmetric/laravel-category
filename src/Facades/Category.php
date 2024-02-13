<?php

namespace JobMetric\Category\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Category\Category
 *
 * @method static array store(array $data)
 * @method static array update(int $category_id, array $data, string $type = 'category')
 * @method static array delete(int $category_id, string $type = 'category')
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
