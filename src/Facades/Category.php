<?php

namespace JobMetric\Category\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JobMetric\Category\Category
 *
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection paginate(string $type, array $filter = [], int $page_limit = 15, array $with = [])
 * @method static \Illuminate\Http\Resources\Json\AnonymousResourceCollection all(string $type, array $filter = [], array $with = [])
 * @method static array store(array $data)
 * @method static array update(int $category_id, array $data)
 * @method static array delete(int $category_id)
 * @method static string getName(int $category_id, bool $concat = true, string $locale = null)
 * @method static array usedIn(int $category_id)
 * @method static bool hasUsed(int $category_id)
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
