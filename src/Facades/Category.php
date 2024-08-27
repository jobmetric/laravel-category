<?php

namespace JobMetric\Category\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Category\Category
 *
 * @method static array store(array $data)
 * @method static array update(int $category_id, array $data)
 * @method static array delete(int $category_id)
 * @method static string getName(int $category_id, bool $concat = true, string $locale = null)
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
        return \JobMetric\Category\Category::class;
    }
}
