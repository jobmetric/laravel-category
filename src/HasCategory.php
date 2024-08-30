<?php

namespace JobMetric\Category;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use JobMetric\Category\Exceptions\CategoryCollectionNotInCategoryAllowTypesException;
use JobMetric\Category\Exceptions\CategoryIsDisableException;
use JobMetric\Category\Exceptions\CategoryNotFoundException;
use JobMetric\Category\Exceptions\InvalidCategoryTypeInCollectionException;
use JobMetric\Category\Exceptions\ModelCategoryContractNotFoundException;
use JobMetric\Category\Http\Resources\CategoryResource;
use JobMetric\Category\Models\Category;
use JobMetric\Category\Models\CategoryRelation;
use Throwable;

/**
 * Trait HasCategory
 *
 * @package JobMetric\Category
 *
 * @property Category[] categories
 *
 * @method morphToMany(string $class, string $string, string $string1)
 * @method categoryAllowTypes()
 */
trait HasCategory
{
    /**
     * boot has category
     *
     * @return void
     * @throws Throwable
     */
    public static function bootHasCategory(): void
    {
        if (!in_array('JobMetric\Category\Contracts\CategoryContract', class_implements(self::class))) {
            throw new ModelCategoryContractNotFoundException(self::class);
        }
    }

    /**
     * category has many relationships
     *
     * @return MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable', config('category.tables.category_relation'))
            ->withPivot('collection')
            ->withTimestamps(['created_at']);
    }

    /**
     * attach category
     *
     * @param int $category_id
     * @param string $collection
     *
     * @return array
     * @throws Throwable
     */
    public function attachCategory(int $category_id, string $collection): array
    {
        /**
         * @var Category $category
         */
        $category = Category::find($category_id);

        if (!$category) {
            throw new CategoryNotFoundException($category_id);
        }

        if (!$category->status) {
            throw new CategoryIsDisableException($category_id);
        }

        $categoryAllowTypes = $this->categoryAllowTypes();

        if (!array_key_exists($collection, $categoryAllowTypes)) {
            throw new CategoryCollectionNotInCategoryAllowTypesException($collection);
        }

        if ($category->type !== $categoryAllowTypes[$collection]['type']) {
            throw new InvalidCategoryTypeInCollectionException($category->type, $collection, $categoryAllowTypes[$collection]['type']);
        }

        $multiple = false;
        if (array_key_exists('multiple', $categoryAllowTypes[$collection])) {
            if ($categoryAllowTypes[$collection]['multiple']) {
                $multiple = true;
            }
        }

        if ($multiple) {
            CategoryRelation::query()->updateOrInsert([
                'category_id' => $category_id,
                'categorizable_id' => $this->id,
                'categorizable_type' => get_class($this),
                'collection' => $collection
            ]);
        } else {
            CategoryRelation::query()->updateOrInsert([
                'categorizable_id' => $this->id,
                'categorizable_type' => get_class($this),
                'collection' => $collection
            ], [
                'category_id' => $category_id
            ]);
        }

        $category->load([
            'translations',
            'categoryRelations'
        ]);

        return [
            'ok' => true,
            'message' => trans('category::base.messages.attached'),
            'data' => CategoryResource::make($category),
            'status' => 200
        ];
    }

    /**
     * attach categories
     *
     * @param array $category_ids
     * @param string $collection
     *
     * @return array
     * @throws Throwable
     */
    public function attachCategories(array $category_ids, string $collection): array
    {
        foreach ($category_ids as $category_id) {
            $this->attachCategory($category_id, $collection);
        }

        return [
            'ok' => true,
            'message' => trans('category::base.messages.multi_attached'),
            'status' => 200
        ];
    }

    /**
     * detach category
     *
     * @param int $category_id
     *
     * @return array
     * @throws Throwable
     */
    public function detachCategory(int $category_id): array
    {
        foreach ($this->categories as $category) {
            if ($category->id == $category_id) {
                $data = CategoryResource::make($category);

                $this->categories()->detach($category_id);

                return [
                    'ok' => true,
                    'message' => trans('category::base.messages.detached'),
                    'data' => $data,
                    'status' => 200
                ];
            }
        }

        throw new CategoryNotFoundException($category_id);
    }

    /**
     * Get category by collection
     *
     * @param string $collection
     *
     * @return MorphToMany
     */
    public function getCategoryByCollection(string $collection): MorphToMany
    {
        return $this->categories()->wherePivot('collection', $collection);
    }
}
