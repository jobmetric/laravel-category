<?php

namespace JobMetric\Category\Tests;

use App\Models\Product;
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
     * @return CategoryModels
     */
    public function create_category_for_has(): CategoryModels
    {
        Category::store([
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

        return CategoryModels::find(1);
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
