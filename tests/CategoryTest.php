<?php

namespace JobMetric\Category\Tests;

use JobMetric\Category\Exceptions\CannotMakeParentSubsetOwnChild;
use JobMetric\Category\Exceptions\CategoryNotFoundException;
use JobMetric\Category\Exceptions\CategoryUsedException;
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
            'type' => 'product_category',
            'parent_id' => null,
            'ordering' => 1,
            'status' => true,
        ]);

        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
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
            'type' => 'product_category',
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
            'type' => 'product_category',
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
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryA['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);

        $categoryB = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);

        $categoryC = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);

        $categoryD = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryD['data']->id,
            'level' => 3,
        ]);

        $categoryE = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'E'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
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
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);

        // check database category path for D
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
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
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryE['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
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
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 1,
        ]);

        // check database category path for D
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
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
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryA['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);

        $categoryB = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);

        $categoryC = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryC['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);

        $categoryD = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryC['data']->id,
            'level' => 2,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryD['data']->id,
            'path_id' => $categoryD['data']->id,
            'level' => 3,
        ]);

        $categoryE = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'E'
            ],
        ]);

        // check database category path
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseHas('category_paths', [
            'type' => 'product_category',
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
            'type' => 'product_category',
            'category_id' => $categoryE['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product_category',
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
        $product = $this->create_product();

        $product->attachCategory($categoryD['data']->id, 'category');

        /**
         * delete category C for error used exception - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C -> delete
         * 4  - |__ |__ |__ D -> attach product
         */
        try {
            $categoryDelete = Category::delete($categoryC['data']->id);

            $this->assertIsArray($categoryDelete);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryUsedException::class, $e);
        }

        /**
         * detach product D - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D -> detach product
         */
        $product->detachCategory($categoryD['data']->id);

        /**
         * delete category C - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C -> delete
         * 4  - |__ |__ |__ D
         */
        $categoryDelete = Category::delete($categoryC['data']->id);

        $this->assertIsArray($categoryDelete);
        $this->assertTrue($categoryDelete['ok']);
        $this->assertEquals($categoryDelete['message'], trans('category::base.messages.deleted'));
        $this->assertInstanceOf(CategoryResource::class, $categoryDelete['data']);
        $this->assertEquals(200, $categoryDelete['status']);

        /**
         * delete category C for not found error - use sample map
         *
         * 1  - A
         * 2  - |__ B
         */
        try {
            $categoryDelete = Category::delete($categoryC['data']->id);

            $this->assertIsArray($categoryDelete);
        } catch (Throwable $e) {
            $this->assertInstanceOf(CategoryNotFoundException::class, $e);
        }

        /**
         * delete category A - use sample map
         *
         * 1  - A -> delete
         * 2  - |__ B
         */
        $categoryDelete = Category::delete($categoryA['data']->id);

        $this->assertIsArray($categoryDelete);
        $this->assertTrue($categoryDelete['ok']);
        $this->assertEquals($categoryDelete['message'], trans('category::base.messages.deleted'));
        $this->assertInstanceOf(CategoryResource::class, $categoryDelete['data']);
        $this->assertEquals(200, $categoryDelete['status']);

        // check database category path for A
        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryA['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);

        // check database category path for B
        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryA['data']->id,
            'level' => 0,
        ]);
        $this->assertDatabaseMissing('category_paths', [
            'type' => 'product_category',
            'category_id' => $categoryB['data']->id,
            'path_id' => $categoryB['data']->id,
            'level' => 1,
        ]);
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
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        $categoryB = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        $categoryC = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        $categoryD = Category::store([
            'type' => 'product_category',
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
    public function test_all()
    {
        /**
         * store product category - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $categoryA = Category::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        $categoryB = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        $categoryC = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'E'
            ],
        ]);

        $productCategories = Category::all('product_category');

        $this->assertCount(5, $productCategories);

        $productCategories->each(function ($productCategory) {
            $this->assertInstanceOf(CategoryResource::class, $productCategory);
        });

        $char = config('category.arrow_icon.' . trans('domi::base.direction'));

        $this->assertEquals('A', $productCategories[0]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[0]);
        $this->assertEquals('A', $productCategories[0]->name_multiple);

        $this->assertEquals('B', $productCategories[1]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[1]);
        $this->assertEquals('A' . $char . 'B', $productCategories[1]->name_multiple);

        $this->assertEquals('C', $productCategories[2]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[2]);
        $this->assertEquals('A' . $char . 'B' . $char . 'C', $productCategories[2]->name_multiple);

        $this->assertEquals('D', $productCategories[3]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[3]);
        $this->assertEquals('A' . $char . 'B' . $char . 'C' . $char . 'D', $productCategories[3]->name_multiple);

        $this->assertEquals('E', $productCategories[4]->name);
        $this->assertArrayHasKey('name_multiple', $productCategories[4]);
        $this->assertEquals('A' . $char . 'E', $productCategories[4]->name_multiple);

        /**
         * store product tag - use sample map
         *
         * 1  - A
         * 2  - B
         * 3  - C
         */
        Category::store([
            'type' => 'product_tag',
            'translation' => [
                'name' => 'A'
            ],
        ]);

        Category::store([
            'type' => 'product_tag',
            'translation' => [
                'name' => 'B'
            ],
        ]);

        Category::store([
            'type' => 'product_tag',
            'translation' => [
                'name' => 'C'
            ],
        ]);

        $productTags = Category::all('product_tag');

        $this->assertCount(3, $productTags);

        $productTags->each(function ($productTag) {
            $this->assertInstanceOf(CategoryResource::class, $productTag);
        });

        $this->assertEquals('A', $productTags[0]->name);
        $this->assertArrayNotHasKey('name_multiple', $productTags[0]);

        $this->assertEquals('B', $productTags[1]->name);
        $this->assertArrayNotHasKey('name_multiple', $productTags[1]);

        $this->assertEquals('C', $productTags[2]->name);
        $this->assertArrayNotHasKey('name_multiple', $productTags[2]);
    }

    /**
     * @throws Throwable
     */
    public function test_pagination()
    {
        /**
         * store product category - use sample map
         *
         * 1  - A
         * 2  - |__ B
         * 3  - |__ |__ C
         * 4  - |__ |__ |__ D
         * 5  - |__ E
         */
        $categoryA = Category::store([
            'type' => 'product_category',
            'parent_id' => null,
            'translation' => [
                'name' => 'A'
            ],
        ]);

        $categoryB = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'B'
            ],
        ]);

        $categoryC = Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryB['data']->id,
            'translation' => [
                'name' => 'C'
            ],
        ]);

        Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryC['data']->id,
            'translation' => [
                'name' => 'D'
            ],
        ]);

        Category::store([
            'type' => 'product_category',
            'parent_id' => $categoryA['data']->id,
            'translation' => [
                'name' => 'E'
            ],
        ]);

        $paginateProductCategories = Category::paginate('product_category');

        $paginateProductCategories->each(function ($paginateProductCategory) {
            $this->assertInstanceOf(CategoryResource::class, $paginateProductCategory);
        });

        $this->assertIsInt($paginateProductCategories->total());
        $this->assertIsInt($paginateProductCategories->perPage());
        $this->assertIsInt($paginateProductCategories->currentPage());
        $this->assertIsInt($paginateProductCategories->lastPage());
        $this->assertIsArray($paginateProductCategories->items());
    }

    /**
     * @throws Throwable
     */
    public function test_used_in()
    {
        $product = $this->create_product();

        // Store a category
        $category_store = $this->create_category_product();

        // Attach the category to the product
        $product->attachCategory($category_store['data']->id, 'category');

        // Get the category used in the product
        $used_in = Category::usedIn($category_store['data']->id);

        $this->assertIsArray($used_in);
        $this->assertTrue($used_in['ok']);
        $this->assertEquals($used_in['message'], trans('category::base.messages.used_in', [
            'count' => 1
        ]));
        $used_in['data']->each(function ($dataUsedIn) {
            $this->assertInstanceOf(CategoryRelationResource::class, $dataUsedIn);
        });
        $this->assertEquals(200, $used_in['status']);

        // Get the category used in the product with a wrong category id
        try {
            $used_in = Category::usedIn(1000);

            $this->assertIsArray($used_in);
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
        $categoryStore = $this->create_category_product();

        // Attach the category to the product
        $product->attachCategory($categoryStore['data']->id, 'category');

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
