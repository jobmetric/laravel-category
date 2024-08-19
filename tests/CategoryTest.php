<?php

namespace JobMetric\Category\Tests;

use JobMetric\Category\Exceptions\CategoryNotFoundException;
use JobMetric\Category\Exceptions\CategoryTypeUsedInException;
use JobMetric\Category\Facades\Category;
use JobMetric\Category\Http\Resources\CategoryRelationResource;
use JobMetric\Category\Http\Resources\CategoryResource;
use Throwable;

class CategoryTest extends BaseCategory
{
    /**
     * @throws Throwable
     */
    public function test_store()
    {
        // store category
        $category = $this->create_category();

        $this->assertIsArray($category);
        $this->assertTrue($category['ok']);
        $this->assertEquals($category['message'], trans('category::base.messages.created'));
        $this->assertInstanceOf(CategoryResource::class, $category['data']);
        $this->assertEquals(201, $category['status']);

        $this->assertDatabaseHas('categories', [
            'type' => 'product',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
        ]);

        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $category['data']->id,
            'path_id' => $category['data']->id,
            'level' => 0,
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'JobMetric\Category\Models\Category',
            'translatable_id' => $category['data']->id,
            'locale' => app()->getLocale(),
            'key' => 'name',
            'value' => 'category name',
        ]);

        // store duplicate name
        $categoryDuplicate = $this->create_category();

        $this->assertIsArray($categoryDuplicate);
        $this->assertFalse($categoryDuplicate['ok']);
        $this->assertEquals($categoryDuplicate['message'], trans('category::base.validation.errors'));
        $this->assertEquals(422, $categoryDuplicate['status']);

        // store with parent category
        $parentCategory = Category::store([
            'type' => 'product',
            'parent_id' => $category['data']->id,
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

        $this->assertIsArray($parentCategory);
        $this->assertTrue($parentCategory['ok']);
        $this->assertEquals($parentCategory['message'], trans('category::base.messages.created'));
        $this->assertInstanceOf(CategoryResource::class, $parentCategory['data']);
        $this->assertEquals(201, $parentCategory['status']);

        // store duplicate name with parent category
        $categoryDuplicate = Category::store([
            'type' => 'product',
            'parent_id' => $category['data']->id,
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

        $this->assertIsArray($categoryDuplicate);
        $this->assertFalse($categoryDuplicate['ok']);
        $this->assertEquals($categoryDuplicate['message'], trans('category::base.validation.errors'));
        $this->assertEquals(422, $categoryDuplicate['status']);
    }

    /**
     * @throws Throwable
     */
    public function test_update()
    {
        // category not found
        try {
            $category = Category::update(1000, [
                'ordering' => 1000,
                'status' => true,
                'translation' => [
                    'name' => 'category name',
                    'description' => 'category description',
                    'meta_title' => 'category meta title',
                    'meta_description' => 'category meta description',
                    'meta_keywords' => 'category meta keywords',
                ],
            ]);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }

        // store a category
        $categoryStore = $this->create_category();

        // update with another name
        $category = Category::update($categoryStore['data']->id, [
            'status' => true,
            'translation' => [
                'name' => 'category name 2',
                'description' => 'category description',
                'meta_title' => 'category meta title',
                'meta_description' => 'category meta description',
                'meta_keywords' => 'category meta keywords',
            ],
        ]);

        $this->assertIsArray($category);
        $this->assertTrue($category['ok']);
        $this->assertEquals($category['message'], trans('category::base.messages.updated'));
        $this->assertInstanceOf(CategoryResource::class, $category['data']);
        $this->assertEquals(200, $category['status']);

        $this->assertDatabaseHas('categorys', [
            'id' => $category['data']->id,
            'type' => 'product',
            'ordering' => 1,
            'status' => true,
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'JobMetric\Category\Models\Category',
            'translatable_id' => $category['data']->id,
            'locale' => app()->getLocale(),
            'key' => 'name',
            'value' => 'category name 2',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_get()
    {
        // store a category
        $categoryStore = $this->create_category();

        // get the category
        $category = Category::get($categoryStore['data']->id);

        $this->assertIsArray($category);
        $this->assertTrue($category['ok']);
        $this->assertEquals($category['message'], trans('category::base.messages.found'));
        $this->assertInstanceOf(CategoryResource::class, $category['data']);
        $this->assertEquals(200, $category['status']);

        $this->assertEquals($category['data']->id, $categoryStore['data']->id);
        $this->assertEquals('product', $category['data']->type);
        $this->assertEquals(1, $category['data']->ordering);
        $this->assertTrue($category['data']->status);

        // get the category with a wrong id
        try {
            $category = Category::get(1000);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_delete()
    {
        // store category
        $categoryStore = $this->create_category();

        // delete the category
        $category = Category::delete($categoryStore['data']->id);

        $this->assertIsArray($category);
        $this->assertTrue($category['ok']);
        $this->assertEquals($category['message'], trans('category::base.messages.deleted'));
        $this->assertEquals(200, $category['status']);

        $this->assertSoftDeleted('categorys', [
            'id' => $categoryStore['data']->id,
        ]);

        // delete the category again
        try {
            $category = Category::delete($categoryStore['data']->id);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }

        // attach the category to the product
        $product = $this->create_product();

        // Store category
        $categoryStore = $this->create_category();

        $product->attachCategory($categoryStore['data']->id, 'product_category');

        // delete the category
        try {
            $category = Category::delete($categoryStore['data']->id);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryTypeUsedInException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_restore()
    {
        // store category
        $categoryStore = $this->create_category();

        // delete the category
        Category::delete($categoryStore['data']->id);

        // restore the category
        $category = Category::restore($categoryStore['data']->id);

        $this->assertIsArray($category);
        $this->assertTrue($category['ok']);
        $this->assertEquals($category['message'], trans('category::base.messages.restored'));
        $this->assertEquals(200, $category['status']);

        $this->assertDatabaseHas('categorys', [
            'id' => $categoryStore['data']->id,
        ]);

        // restore the category again
        try {
            $category = Category::restore($categoryStore['data']->id);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_force_delete()
    {
        // store category
        $categoryStore = $this->create_category();

        // delete the category
        Category::delete($categoryStore['data']->id);

        // force delete category
        $category = Category::forceDelete($categoryStore['data']->id);

        $this->assertIsArray($category);
        $this->assertTrue($category['ok']);
        $this->assertEquals($category['message'], trans('category::base.messages.permanently_deleted'));
        $this->assertEquals(200, $category['status']);

        $this->assertDatabaseMissing('categorys', [
            'id' => $categoryStore['data']->id,
        ]);

        // force delete category again
        try {
            $category = Category::forceDelete($categoryStore['data']->id);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_all()
    {
        // Store a category
        $this->create_category();

        // Get the categorys
        $getCategorys = Category::all();

        $this->assertCount(1, $getCategorys);

        $getCategorys->each(function ($category) {
            $this->assertInstanceOf(CategoryResource::class, $category);
        });
    }

    /**
     * @throws Throwable
     */
    public function test_pagination()
    {
        // Store a category
        $this->create_category();

        // Paginate the categorys
        $paginateCategorys = Category::paginate();

        $this->assertCount(1, $paginateCategorys);

        $paginateCategorys->each(function ($category) {
            $this->assertInstanceOf(CategoryResource::class, $category);
        });

        $this->assertIsInt($paginateCategorys->total());
        $this->assertIsInt($paginateCategorys->perPage());
        $this->assertIsInt($paginateCategorys->currentPage());
        $this->assertIsInt($paginateCategorys->lastPage());
        $this->assertIsArray($paginateCategorys->items());
    }

    /**
     * @throws Throwable
     */
    public function test_used_in()
    {
        $product = $this->create_product();

        // Store a category
        $categoryStore = $this->create_category();

        // Attach the category to the product
        $attachCategory = $product->attachCategory($categoryStore['data']->id, 'product_category');

        $this->assertIsArray($attachCategory);
        $this->assertTrue($attachCategory['ok']);
        $this->assertEquals($attachCategory['message'], trans('category::base.messages.attached'));
        $this->assertInstanceOf(CategoryResource::class, $attachCategory['data']);
        $this->assertEquals(200, $attachCategory['status']);

        // Get the category used in the product
        $usedIn = Category::usedIn($categoryStore['data']->id);

        $this->assertIsArray($usedIn);
        $this->assertTrue($usedIn['ok']);
        $this->assertEquals($usedIn['message'], trans('category::base.messages.used_in', [
            'count' => 1
        ]));
        $usedIn['data']->each(function ($dataUsedIn) {
            $this->assertInstanceOf(CategoryRelationResource::class, $dataUsedIn);
        });
        $this->assertEquals(200, $usedIn['status']);

        // Get the category used in the product with a wrong category id
        try {
            $usedIn = Category::usedIn(1000);

            $this->assertIsArray($usedIn);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_has_used()
    {
        $product = $this->create_product();

        // Store a category
        $categoryStore = $this->create_category();

        // Attach the category to the product
        $attachCategory = $product->attachCategory($categoryStore['data']->id, 'product_category');

        $this->assertIsArray($attachCategory);
        $this->assertTrue($attachCategory['ok']);
        $this->assertEquals($attachCategory['message'], trans('category::base.messages.attached'));
        $this->assertInstanceOf(CategoryResource::class, $attachCategory['data']);
        $this->assertEquals(200, $attachCategory['status']);

        // check has used in
        $usedIn = Category::hasUsed($categoryStore['data']->id);

        $this->assertTrue($usedIn);

        // check with wrong category id
        try {
            $usedIn = Category::hasUsed(1000);

            $this->assertIsArray($usedIn);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }
    }
}
