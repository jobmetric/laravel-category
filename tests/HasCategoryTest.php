<?php

namespace JobMetric\Category\Tests;

use App\Models\Product;
use JobMetric\Category\Exceptions\CategoryCollectionNotInCategoryAllowTypesException;
use JobMetric\Category\Exceptions\CategoryIsDisableException;
use JobMetric\Category\Exceptions\InvalidCategoryTypeInCollectionException;
use JobMetric\Category\Http\Resources\CategoryResource;
use JobMetric\Category\Models\Category;
use Throwable;

class HasCategoryTest extends BaseCategory
{
    /**
     * @throws Throwable
     */
    public function test_categories_trait_relationship()
    {
        $product = new Product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $product->categories());
    }

    /**
     * @throws Throwable
     */
    public function test_attach_category(): void
    {
        /**
         * @var Product $product
         * @var Category $category_product_1
         * @var Category $category_product_2
         * @var Category $category_product_tag_1
         * @var Category $category_product_tag_2
         * @var Category $category_product_tag_3
         */
        $product = $this->create_product();
        $category_product_1 = $this->create_category_for_has('product_category', 'product 1');
        $category_product_2 = $this->create_category_for_has('product_category', 'product 2');
        $category_product_tag_1 = $this->create_category_for_has('product_tag', 'product tag 1');
        $category_product_tag_2 = $this->create_category_for_has('product_tag', 'product tag 2');
        $category_product_tag_3 = $this->create_category_for_has('product_tag', 'product tag 3', false);

        // attach normally single collection
        $attach_1 = $product->attachCategory($category_product_1->id, 'product');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_1['ok']);
        $this->assertEquals($attach_1['message'], trans('category::base.messages.attached'));
        $this->assertInstanceOf(CategoryResource::class, $attach_1['data']);
        $this->assertEquals(200, $attach_1['status']);

        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_1->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'product'
        ]);

        $attach_2 = $product->attachCategory($category_product_2->id, 'product');

        $this->assertIsArray($attach_2);
        $this->assertTrue($attach_2['ok']);
        $this->assertEquals($attach_2['message'], trans('category::base.messages.attached'));
        $this->assertInstanceOf(CategoryResource::class, $attach_2['data']);
        $this->assertEquals(200, $attach_2['status']);

        $this->assertDatabaseMissing(config('category.tables.category_relation'), [
            'category_id' => $category_product_1->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'product'
        ]);

        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_2->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'product'
        ]);

        // attach normally multiple collection
        $attach_1 = $product->attachCategory($category_product_tag_1->id, 'tag');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_1['ok']);
        $this->assertEquals($attach_1['message'], trans('category::base.messages.attached'));
        $this->assertInstanceOf(CategoryResource::class, $attach_1['data']);
        $this->assertEquals(200, $attach_1['status']);

        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_tag_1->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'tag'
        ]);

        $attach_2 = $product->attachCategory($category_product_tag_2->id, 'tag');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_2['ok']);
        $this->assertEquals($attach_2['message'], trans('category::base.messages.attached'));
        $this->assertInstanceOf(CategoryResource::class, $attach_2['data']);
        $this->assertEquals(200, $attach_2['status']);

        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_tag_1->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'tag'
        ]);
        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_tag_2->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'tag'
        ]);

        // attach invalid collection
        try {
            $product->attachCategory($category_product_1->id, 'invalid_collection');
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryCollectionNotInCategoryAllowTypesException::class, $e);
        }

        // attach invalid collection type
        try {
            $product->attachCategory($category_product_1->id, 'tag');
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidCategoryTypeInCollectionException::class, $e);
        }

        // attach disabled category
        try {
            $product->attachCategory($category_product_tag_3->id, 'tag');
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryIsDisableException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_attach_categories(): void
    {
        /**
         * @var Product $product
         * @var Category $category_product_tag_1
         * @var Category $category_product_tag_2
         * @var Category $category_product_tag_3
         */
        $product = $this->create_product();
        $category_product_tag_1 = $this->create_category_for_has('product_tag', 'product tag 1');
        $category_product_tag_2 = $this->create_category_for_has('product_tag', 'product tag 2');
        $category_product_tag_3 = $this->create_category_for_has('product_tag', 'product tag 3');

        // attach multiple category
        $attach_1 = $product->attachCategories([
            $category_product_tag_1->id,
            $category_product_tag_2->id,
            $category_product_tag_3->id,
        ], 'tag');

        $this->assertIsArray($attach_1);
        $this->assertTrue($attach_1['ok']);
        $this->assertEquals($attach_1['message'], trans('category::base.messages.multi_attached'));
        $this->assertEquals(200, $attach_1['status']);

        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_tag_1->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'tag'
        ]);
        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_tag_2->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'tag'
        ]);
        $this->assertDatabaseHas(config('category.tables.category_relation'), [
            'category_id' => $category_product_tag_3->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'tag'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_detach_category(): void
    {
        /**
         * @var Product $product
         * @var Category $category_product
         */
        $product = $this->create_product();
        $category_product = $this->create_category_for_has('product_category', 'product');

        // attach category
        $product->attachCategory($category_product->id, 'product');

        $detach = $product->detachCategory($category_product->id);

        $this->assertIsArray($detach);

        $this->assertDatabaseMissing(config('category.tables.category_relation'), [
            'category_id' => $category_product->id,
            'categorizable_id' => $product->id,
            'categorizable_type' => Product::class,
            'collection' => 'product'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_get_category_by_collection(): void
    {
        $product = new Product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $product->getCategoryByCollection('product'));
    }
}
