<?php

namespace JobMetric\Category;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Category\Events\CategoryDeleteEvent;
use JobMetric\Category\Events\CategoryStoreEvent;
use JobMetric\Category\Events\CategoryUpdateEvent;
use JobMetric\Category\Exceptions\CannotMakeParentSubsetOwnChild;
use JobMetric\Category\Exceptions\CategoryNotFoundException;
use JobMetric\Category\Exceptions\CategoryUsedException;
use JobMetric\Category\Http\Requests\StoreCategoryRequest;
use JobMetric\Category\Http\Requests\UpdateCategoryRequest;
use JobMetric\Category\Http\Resources\CategoryRelationResource;
use JobMetric\Category\Http\Resources\CategoryResource;
use JobMetric\Category\Models\Category as CategoryModel;
use JobMetric\Category\Models\CategoryPath;
use JobMetric\Category\Models\CategoryRelation;
use JobMetric\Translation\Models\Translation;
use Throwable;

class Category
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Setting instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Store the specified category.
     *
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function store(array $data): array
    {
        $validator = Validator::make($data, (new StoreCategoryRequest)->setType($data['type'] ?? null)->setParentId($data['parent_id'] ?? null)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('category::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();
        }

        return DB::transaction(function () use ($data) {
            $categoryTypes = getCategoryType();
            $hierarchical = $categoryTypes[$data['type']]['hierarchical'];

            $category = new CategoryModel;
            $category->type = $data['type'];
            $category->parent_id = $data['parent_id'] ?? null;
            $category->ordering = $data['ordering'] ?? 0;
            $category->status = $data['status'] ?? true;
            $category->save();

            $category->translate(app()->getLocale(), [
                'name' => $data['translation']['name'],
                'description' => $data['translation']['description'] ?? null,
                'meta_title' => $data['translation']['meta_title'] ?? null,
                'meta_description' => $data['translation']['meta_description'] ?? null,
                'meta_keywords' => $data['translation']['meta_keywords'] ?? null,
            ]);

            if ($hierarchical) {
                $level = 0;

                $paths = CategoryPath::query()->select('path_id')->where([
                    'category_id' => $category->parent_id
                ])->orderBy('level')->get()->toArray();

                $paths[] = [
                    'path_id' => $category->id
                ];

                foreach ($paths as $path) {
                    $categoryPath = new CategoryPath;
                    $categoryPath->type = $category->type;
                    $categoryPath->category_id = $category->id;
                    $categoryPath->path_id = $path['path_id'];
                    $categoryPath->level = $level++;
                    $categoryPath->save();

                    unset($categoryPath);
                }
            }

            event(new CategoryStoreEvent($category, $data, $hierarchical));

            return [
                'ok' => true,
                'message' => trans('category::base.messages.created'),
                'data' => CategoryResource::make($category),
                'status' => 201
            ];
        });
    }

    /**
     * Update the specified category.
     *
     * @param int $category_id
     * @param array $data
     *
     * @return array
     * @throws Throwable
     */
    public function update(int $category_id, array $data): array
    {
        /**
         * @var CategoryModel $category
         */
        $category = CategoryModel::find($category_id);

        if (!$category) {
            throw new CategoryNotFoundException($category_id);
        }

        $validator = Validator::make($data, (new UpdateCategoryRequest)->setType($category->type)->setCategoryId($category_id)->setData($data)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('category::base.validation.errors'),
                'errors' => $errors,
                'status' => 422
            ];
        } else {
            $data = $validator->validated();
        }

        return DB::transaction(function () use ($category_id, $data, $category) {
            $categoryTypes = getCategoryType();
            $hierarchical = $categoryTypes[$category->type]['hierarchical'];

            $change_parent_id = false;
            if (array_key_exists('parent_id', $data) && $category->parent_id != $data['parent_id'] && $hierarchical) {
                // If the parent_id is changed, the path of the category must be updated.

                // You cannot make a parent a subset of its own child.
                if (CategoryPath::query()->where([
                    'type' => $category->type,
                    'category_id' => $data['parent_id'],
                    'path_id' => $category_id
                ])->exists()) {
                    throw new CannotMakeParentSubsetOwnChild;
                }

                $category->parent_id = $data['parent_id'];

                $change_parent_id = true;
            }

            if (array_key_exists('ordering', $data)) {
                $category->ordering = $data['ordering'];
            }

            if (array_key_exists('status', $data)) {
                $category->status = $data['status'];
            }

            $category->save();

            if (array_key_exists('translation', $data)) {
                $trnas = [];
                if (array_key_exists('name', $data['translation'])) {
                    $trnas['name'] = $data['translation']['name'];
                }

                if (array_key_exists('description', $data['translation'])) {
                    $trnas['description'] = $data['translation']['description'];
                }

                if (array_key_exists('meta_title', $data['translation'])) {
                    $trnas['meta_title'] = $data['translation']['meta_title'];
                }

                if (array_key_exists('meta_description', $data['translation'])) {
                    $trnas['meta_description'] = $data['translation']['meta_description'];
                }

                if (array_key_exists('meta_keywords', $data['translation'])) {
                    $trnas['meta_keywords'] = $data['translation']['meta_keywords'];
                }

                $category->translate(app()->getLocale(), $trnas);
            }

            if ($change_parent_id) {
                $paths = CategoryPath::query()->where([
                    'type' => $category->type,
                    'path_id' => $category_id,
                ])->get()->toArray();

                foreach ($paths as $path) {
                    // Delete the path below the current one
                    CategoryPath::query()->where([
                        'type' => $category->type,
                        'category_id' => $path['category_id']
                    ])->where('level', '<', $path['level'])->delete();

                    $item_paths = [];

                    // Get the nodes new parents
                    $nodes = CategoryPath::query()->where([
                        'type' => $category->type,
                        'category_id' => $category->parent_id
                    ])->orderBy('level')->get()->toArray();

                    foreach ($nodes as $node) {
                        $item_paths[] = $node['path_id'];
                    }

                    // Get what's left of the nodes current path
                    $left_nodes = CategoryPath::query()->where([
                        'type' => $category->type,
                        'category_id' => $path['category_id']
                    ])->orderBy('level')->get()->toArray();

                    foreach ($left_nodes as $left_node) {
                        $item_paths[] = $left_node['path_id'];
                    }

                    // Combine the paths with a new level
                    $level = 0;
                    foreach ($item_paths as $item_path) {
                        CategoryPath::query()->updateOrInsert([
                            'type' => $category->type,
                            'category_id' => $path['category_id'],
                            'path_id' => $item_path,
                        ], [
                            'level' => $level++
                        ]);
                    }
                }
            }

            event(new CategoryUpdateEvent($category, $data, $change_parent_id));

            return [
                'ok' => true,
                'message' => trans('category::base.messages.updated'),
                'data' => CategoryResource::make($category),
                'status' => 200
            ];
        });
    }

    /**
     * Delete the specified category.
     *
     * @param int $category_id
     *
     * @return array
     * @throws Throwable
     */
    public function delete(int $category_id): array
    {
        /**
         * @var CategoryModel $category
         */
        $category = CategoryModel::find($category_id);

        if (!$category) {
            throw new CategoryNotFoundException($category_id);
        }

        $data = CategoryResource::make($category);

        return DB::transaction(function () use ($category_id, $category, $data) {
            $categoryTypes = getCategoryType();
            $hierarchical = $categoryTypes[$category->type]['hierarchical'];

            if ($hierarchical) {
                $category_ids = CategoryPath::query()->where([
                    'type' => $category->type,
                    'path_id' => $category_id
                ])->pluck('category_id')->toArray();

                // @todo: change number to name for exception message
                $flag_number = false;
                foreach ($category_ids as $item) {
                    if ($this->hasUsed($item)) {
                        $flag_number = $item;
                        break;
                    }
                }

                if ($flag_number) {
                    throw new CategoryUsedException($flag_number);
                }

                CategoryPath::query()->where('type', $category->type)->whereIn('category_id', $category_ids)->delete();

                CategoryModel::query()->whereIn('id', $category_ids)->get()->each(function ($item) {
                    /**
                     * @var CategoryModel $item
                     */
                    $item->forgetTranslations();

                    $item->delete();
                });
            } else {
                if ($this->hasUsed($category_id)) {
                    throw new CategoryUsedException($category_id);
                }

                $category->forgetTranslations();
                $category->delete();
            }

            event(new CategoryDeleteEvent($category));

            return [
                'ok' => true,
                'message' => trans('category::base.messages.deleted'),
                'data' => $data,
                'status' => 200
            ];
        });
    }

    public function getCategoryName(int $category_id, string $locale = 'en', string $type = 'category'): string|array
    {
        /**
         * @var CategoryModel $category
         */
        $category = CategoryModel::query()->where([
            'id' => $category_id,
            'type' => $type
        ])->first();

        if (!$category) {
            return [
                'ok' => false,
                'message' => trans('category::base.validation.errors'),
                'errors' => [
                    trans('category::base.validation.category_not_found')
                ]
            ];
        }

        $_category = new CategoryModel;
        $_category_path = new CategoryPath;
        $_category_translation = new Translation;

        $query = DB::table($_category_path->getTable() . ' as cp');

        //$query->selectRaw("string_agg('ct1.name ORDER BY cp.level', '»') as name");
        $query->selectSub("SELECT GROUP_CONCAT(`" . DB::getTablePrefix() . "ct1` . `value` ORDER BY `" . DB::getTablePrefix() . "cp` . `LEVEL` SEPARATOR '  »  ' )", "name");

        $query->join($_category->getTable() . ' as c1', function ($q) {
            $q->on('cp.category_id', '=', 'c1.id');
            $q->on('c1.type', '=', 'cp.type');
        });
        $query->join($_category->getTable() . ' as c2', function ($q) {
            $q->on('cp.path_id', '=', 'c2.id');
            $q->on('c2.type', '=', 'cp.type');
        });

        $query->join($_category_translation->getTable() . ' as ct1', function ($q) use ($_category, $locale) {
            $q->on('cp.path_id', '=', 'ct1.translatable_id')
                ->on('ct1.translatable_type', '=', DB::raw("'" . str_replace('\\', '\\\\', get_class($_category)) . "'"))
                ->on('ct1.locale', '=', DB::raw("'" . $locale . "'"))
                ->on('ct1.key', '=', DB::raw("'title'"));
        });

        $query->join($_category_translation->getTable() . ' as ct2', function ($q) use ($_category, $locale) {
            $q->on('c2.id', '=', 'ct2.translatable_id')
                ->on('ct2.translatable_type', '=', DB::raw("'" . str_replace('\\', '\\\\', get_class($_category)) . "'"))
                ->on('ct2.locale', '=', DB::raw("'" . $locale . "'"))
                ->on('ct2.key', '=', DB::raw("'title'"));
        });

        $query->where([
            'cp.type' => $type,
            'cp.category_id' => $category_id,
        ]);

        $query->groupBy('cp.category_id');

        $result = $query->first();

        return $result->name;
    }

    /**
     * Used In category
     *
     * @param int $category_id
     *
     * @return AnonymousResourceCollection
     * @throws Throwable
     */
    public function usedIn(int $category_id): AnonymousResourceCollection
    {
        /**
         * @var CategoryModel $category
         */
        $category = CategoryModel::find($category_id);

        if (!$category) {
            throw new CategoryNotFoundException($category_id);
        }

        $category_relations = CategoryRelation::query()->where([
            'category_id' => $category_id
        ])->get();

        return CategoryRelationResource::collection($category_relations);
    }

    /**
     * Has Used category
     *
     * @param int $category_id
     *
     * @return bool
     * @throws Throwable
     */
    public function hasUsed(int $category_id): bool
    {
        /**
         * @var CategoryModel $category
         */
        $category = CategoryModel::find($category_id);

        if (!$category) {
            throw new CategoryNotFoundException($category_id);
        }

        return CategoryRelation::query()->where([
            'category_id' => $category_id
        ])->exists();
    }
}
