<?php

namespace JobMetric\Category\Tests;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use JobMetric\Category\Facades\Category;
use JobMetric\Category\Models\Category as CategoryModels;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class BaseCategory extends BaseTestCase
{
    /**
     * create a fake product
     *
     * @return Product
     */
    public function create_product(): Product
    {
        return Product::factory()->create();
    }

    /**
     * create a fake category
     *
     * @param string $type
     * @param string $name
     * @param bool $status
     *
     * @return Model
     */
    public function create_category_for_has(string $type, string $name, bool $status = true): Model
    {
        $category = Category::store([
            'type' => $type,
            'status' => $status,
            'translation' => [
                'name' => $name,
            ],
        ]);

        return CategoryModels::find($category['data']->id);
    }

    /**
     * create a fake category
     *
     * @return array
     */
    public function create_category_product(): array
    {
        return Category::store([
            'type' => 'product',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'name' => 'category name',
                'description' => 'category description',
                'meta_title' => 'category meta title',
                'meta_description' => 'category meta description',
                'meta_keywords' => 'category meta keywords',
            ],
        ]);
    }

    /**
     * create a fake category
     *
     * @return array
     */
    public function create_category_product_tag(): array
    {
        return Category::store([
            'type' => 'product_tag',
            'ordering' => 1,
            'status' => true,
            'translation' => [
                'name' => 'tag name',
                'description' => 'tag description',
                'meta_title' => 'tag meta title',
                'meta_description' => 'tag meta description',
                'meta_keywords' => 'tag meta keywords',
            ],
        ]);
    }
}
