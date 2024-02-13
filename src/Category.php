<?php

namespace JobMetric\Category;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JobMetric\Category\Events\Category\CategoryDeleteEvent;
use JobMetric\Category\Events\Category\CategoryStoreEvent;
use JobMetric\Category\Events\Category\CategoryUpdateEvent;
use JobMetric\Category\Http\Requests\StoreCategoryRequest;
use JobMetric\Category\Http\Requests\UpdateCategoryRequest;
use JobMetric\Category\Models\Category as CategoryModel;
use JobMetric\Category\Models\CategoryPath;
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
        $validator = Validator::make($data, (new StoreCategoryRequest)->setData($data)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('category::base.validation.errors'),
                'errors' => $errors
            ];
        }

        return DB::transaction(function () use ($data) {
            $category = new CategoryModel;
            $category->slug = Str::slug($data['slug'] ?? null);
            $category->parent_id = $data['parent_id'] ?? 0;
            $category->type = $data['type'] ?? 'category';
            $category->ordering = $data['ordering'] ?? 0;
            $category->status = $data['status'] ?? true;
            $category->save();

            foreach ($data['translations'] ?? [] as $locale => $value) {
                $category->translate($locale, $value);
            }

            $level = 0;

            $paths = CategoryPath::query()->where([
                'category_id' => $category->parent_id,
                'type' => $category->type
            ])->orderBy('level')->get()->toArray();

            $paths[] = last($paths);
            $paths[count($paths) - 1]['path_id'] = $category->id;

            foreach ($paths as $path) {
                $categoryPath = new CategoryPath;
                $categoryPath->type = $category->type;
                $categoryPath->category_id = $category->id;
                $categoryPath->path_id = $path['path_id'];
                $categoryPath->level = $level++;
                $categoryPath->save();

                unset($categoryPath);
            }

            event(new CategoryStoreEvent($category, $data));

            return [
                'ok' => true,
                'message' => trans('category::base.messages.created'),
                'data' => $category
            ];
        });
    }

    /**
     * Update the specified category.
     *
     * @param int $category_id
     * @param array $data
     * @param string $type
     * @return array
     */
    public function update(int $category_id, array $data, string $type = 'category'): array
    {
        $validator = Validator::make($data, (new UpdateCategoryRequest)->setType($type)->setCategoryId($category_id)->setData($data)->rules());
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return [
                'ok' => false,
                'message' => trans('category::base.validation.errors'),
                'errors' => $errors
            ];
        }

        return DB::transaction(function () use ($category_id, $data, $type) {
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

            if (isset($data['slug'])) {
                $category->slug = Str::slug($data['slug']);
            }

            if (isset($data['ordering'])) {
                $category->ordering = $data['ordering'];
            }

            if (isset($data['status'])) {
                $category->status = $data['status'];
            }

            $change_parent_id = false;
            if (isset($data['parent_id']) && $category->parent_id != $data['parent_id']) {
                $category->parent_id = $data['parent_id'];

                $change_parent_id = true;
            }

            $category->save();

            foreach ($data['translations'] ?? [] as $locale => $value) {
                $category->translate($locale, $value);
            }

            if ($change_parent_id) {
                $paths = CategoryPath::query()->where([
                    'type' => $type,
                    'path_id' => $category_id,
                ])->orderBy('level')->get()->toArray();

                if (empty($paths)) {
                    CategoryPath::query()->where([
                        'type' => $type,
                        'category_id' => $category_id,
                    ])->get()->each(function ($item) {
                        $item->delete();
                    });

                    // Fix for records with no paths
                    $level = 0;

                    $paths = CategoryPath::query()->where([
                        'type' => $type,
                        'category_id' => $category->parent_id,
                    ])->orderBy('level')->get()->toArray();

                    foreach ($paths as $path) {
                        $categoryPath = new CategoryPath;
                        $categoryPath->type = $type;
                        $categoryPath->category_id = $category_id;
                        $categoryPath->path_id = $path['path_id'];
                        $categoryPath->level = $level++;
                        $categoryPath->save();

                        unset($categoryPath);
                    }

                    $categoryPath = new CategoryPath;
                    $categoryPath->type = $type;
                    $categoryPath->category_id = $category_id;
                    $categoryPath->path_id = $category_id;
                    $categoryPath->level = $level;
                    $categoryPath->save();
                } else {
                    foreach ($paths as $path) {
                        // Delete the path below the current one
                        CategoryPath::query()->where([
                            'type' => $type,
                            'category_id' => $path['category_id']
                        ])->where('level', '<', $path['level'])->get()->each(function ($item) {
                            $item->delete();
                        });

                        $item_paths = [];

                        // Get the nodes new parents
                        $nodes = CategoryPath::query()->where([
                            'type' => $type,
                            'category_id' => $category->parent_id
                        ])->orderBy('level')->get()->toArray();

                        foreach ($nodes as $node) {
                            $item_paths[] = $node['path_id'];
                        }

                        // Get what's left of the nodes current path
                        $left_nodes = CategoryPath::query()->where([
                            'type' => $type,
                            'category_id' => $path['category_id']
                        ])->orderBy('level')->get()->toArray();

                        foreach ($left_nodes as $left_node) {
                            $item_paths[] = $left_node['path_id'];
                        }

                        // Combine the paths with a new level
                        $level = 0;
                        foreach ($item_paths as $item_path) {
                            CategoryPath::query()->updateOrInsert([
                                'type' => $type,
                                'category_id' => $path['category_id'],
                                'path_id' => $item_path,
                            ], [
                                'level' => $level++
                            ]);
                        }
                    }
                }
            }

            event(new CategoryUpdateEvent($category, $data, $change_parent_id));

            return [
                'ok' => true,
                'message' => trans('category::base.messages.updated'),
                'data' => $category
            ];
        });
    }

    /**
     * Delete the specified category.
     *
     * @param int $category_id
     * @param string $type
     * @return array
     */
    public function delete(int $category_id, string $type = 'category'): array
    {
        return DB::transaction(function () use ($category_id, $type) {
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

            CategoryPath::query()->where([
                'type' => $type,
                'category_id' => $category_id
            ])->get()->each(function ($item) {
                $item->delete();
            });

            $paths = CategoryPath::query()->where([
                'type' => $type,
                'path_id' => $category_id
            ])->get()->toArray();

            foreach ($paths as $path) {
                self::delete($path['category_id'], $type);
            }

            event(new CategoryDeleteEvent($category));

            $category->forgetTranslations();
            $category->delete();

            return [
                'ok' => true,
                'message' => trans('category::base.messages.deleted')
            ];
        });
    }
}
