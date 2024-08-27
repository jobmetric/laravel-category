<?php

namespace JobMetric\Category\Tests;

use JobMetric\Category\Exceptions\CannotMakeParentSubsetOwnChild;
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
        $category = $this->create_category_product();

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
        $categoryDuplicate = $this->create_category_product();

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

        // store product tag category
        $categoryProductTag = $this->create_category_product_tag();

        $this->assertIsArray($categoryProductTag);
        $this->assertTrue($categoryProductTag['ok']);
        $this->assertEquals($categoryProductTag['message'], trans('category::base.messages.created'));
        $this->assertInstanceOf(CategoryResource::class, $categoryProductTag['data']);
        $this->assertEquals(201, $categoryProductTag['status']);

        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product_tag',
            'category_id' => $categoryProductTag['data']->id,
            'path_id' => $categoryProductTag['data']->id,
            'level' => 0,
        ]);

        // duplicate product tag category
        $categoryDuplicate = $this->create_category_product_tag();

        $this->assertIsArray($categoryDuplicate);
        $this->assertFalse($categoryDuplicate['ok']);
        $this->assertEquals($categoryDuplicate['message'], trans('category::base.validation.errors'));
        $this->assertEquals(422, $categoryDuplicate['status']);

        // store product tag category with parent category
        $parentCategory = Category::store([
            'type' => 'product_tag',
            'parent_id' => $categoryProductTag['data']->id,
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

        $this->assertDatabaseHas('categories', [
            'type' => 'product_tag',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
        ]);
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

        /**
         * store category - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $categoryA = Category::store([
            'type' => 'product',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryA['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);

        $categoryB = Category::store([
            'type' => 'product',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);

        $categoryC = Category::store([
            'type' => 'product',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);

        $categoryD = Category::store([
            'type' => 'product',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryD['data']->id,
            'level' => 3,
        ]);

        $categoryE = Category::store([
            'type' => 'product',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'E'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);

        /**
         * update category move C to E - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 5  - |__ E
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         */
        $categoryUpdate = Category::update($categoryC['data']->id, [
            'parent_id' => $categoryE['data']->id
        ]);

        $this->assertIsArray($categoryUpdate);
        $this->assertTrue($categoryUpdate['ok']);
        $this->assertEquals($categoryUpdate['message'], trans('category::base.messages.updated'));
        $this->assertInstanceOf(CategoryResource::class, $categoryUpdate['data']);
        $this->assertEquals(200, $categoryUpdate['status']);

        // check database category path for C
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);

        // check database category path for D
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryD['data']->id,
            'level' => 3,
        ]);

        /**
         * update category move B to E - use sample map
         *
         * 1  - A
         * 5  - |__ E
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 2  - |__ |__ B
         */
        $categoryUpdate = Category::update($categoryB['data']->id, [
            'parent_id' => $categoryE['data']->id
        ]);

        $this->assertIsArray($categoryUpdate);
        $this->assertTrue($categoryUpdate['ok']);
        $this->assertEquals($categoryUpdate['message'], trans('category::base.messages.updated'));
        $this->assertInstanceOf(CategoryResource::class, $categoryUpdate['data']);
        $this->assertEquals(200, $categoryUpdate['status']);

        // check database category path for B
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 2,
        ]);

        /**
         * update category move C to A - use sample map
         *
         * 1  - A
         * 5  - |__ E
         * 2  - |__ |__ B
         * 3  - |__ C
         * 4  - |__ |__ D
         */
        $categoryUpdate = Category::update($categoryC['data']->id, [
            'parent_id' => $categoryA['data']->id
        ]);

        $this->assertIsArray($categoryUpdate);
        $this->assertTrue($categoryUpdate['ok']);
        $this->assertEquals($categoryUpdate['message'], trans('category::base.messages.updated'));
        $this->assertInstanceOf(CategoryResource::class, $categoryUpdate['data']);
        $this->assertEquals(200, $categoryUpdate['status']);

        // check database category path for C
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 1,
        ]);

        // check database category path for D
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryD['data']->id,
            'level' => 2,
        ]);

        // Entering an illegitimate relationship from A to B
        try {
            $categoryUpdate = Category::update($categoryA['data']->id, [
                'parent_id' => $categoryB['data']->id
            ]);

            $this->assertIsArray($categoryUpdate);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CannotMakeParentSubsetOwnChild::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function test_delete()
    {
        // category not found
        try {
            $category = Category::delete(1000);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }

        /**
         * store category - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $categoryA = Category::store([
            'type' => 'product',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryA['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);

        $categoryB = Category::store([
            'type' => 'product',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);

        $categoryC = Category::store([
            'type' => 'product',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);

        $categoryD = Category::store([
            'type' => 'product',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryD['data']->id,
            'level' => 3,
        ]);

        $categoryE = Category::store([
            'type' => 'product',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'E'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);

        /**
         * delete category E - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E -> delete
         */
        $categoryDelete = Category::delete($categoryE['data']->id);

        $this->assertIsArray($categoryDelete);
        $this->assertTrue($categoryDelete['ok']);
        $this->assertEquals($categoryDelete['message'], trans('category::base.messages.deleted'));
        $this->assertInstanceOf(CategoryResource::class, $categoryDelete['data']);
        $this->assertEquals(200, $categoryDelete['status']);

        // check database category path for E
        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);

        /**
         * attach product to D - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D -> attach product
         */

        /**
         * delete category C for error used exception - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C -> delete
         * 4  - |__ |__ |__ D -> attach product
         */

        /**
         * detach product D - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D -> detach product
         */

        /**
         * delete category C - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C -> delete
         * 4  - |__ |__ |__ D
         */

        /**
         * delete category C for not found error - use sample map
         *
         * 1  - A
         * 2  - |__ B
         */

        /**
         * delete category A - use sample map
         *
         * 1  - A -> delete
         * 2  - |__ B
         */
    }

    /**
     * @throws Throwable
     */
    public function test_get_name()
    {
        // category not found
        try {
            $category = Category::delete(1000);

            $this->assertIsArray($category);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }

        /**
         * store category - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         */
        $categoryA = Category::store([
            'type' => 'product',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        $categoryB = Category::store([
            'type' => 'product',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        $categoryC = Category::store([
            'type' => 'product',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        $categoryD = Category::store([
            'type' => 'product',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        // get name of category D full path
        $categoryName = Category::getName($categoryD['data']->id);

        $this->assertIsString($categoryName);
        $this->assertEquals('A ► B ► C ► D', $categoryName);

        // get name of category D single name
        $categoryName = Category::getName($categoryD['data']->id, false);

        $this->assertIsString($categoryName);
        $this->assertEquals('D', $categoryName);
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
