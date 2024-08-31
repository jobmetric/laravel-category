<?php

namespace JobMetric\Category;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JobMetric\Category\Events\CategoryDeleteEvent;
use JobMetric\Category\Events\CategoryStoreEvent;
use JobMetric\Category\Events\CategoryUpdateEvent;
use JobMetric\Category\Exceptions\CannotMakeParentSubsetOwnChild;
use JobMetric\Category\Exceptions\CategoryNotFoundException;
use JobMetric\Category\Exceptions\CategoryTypeNotMatchException;
use JobMetric\Category\Exceptions\CategoryUsedException;
use JobMetric\Category\Http\Requests\StoreCategoryRequest;
use JobMetric\Category\Http\Requests\UpdateCategoryRequest;
use JobMetric\Category\Http\Resources\CategoryRelationResource;
use JobMetric\Category\Http\Resources\CategoryResource;
use JobMetric\Category\Models\Category as CategoryModel;
use JobMetric\Category\Models\CategoryPath;
use JobMetric\Category\Models\CategoryRelation;
use JobMetric\Media\Models\Media;
use JobMetric\Translation\Models\Translation;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
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
     * Get the specified category.
     *
     * @param string $type
     * @param array $filter
     * @param array $with
     *
     * @return QueryBuilder
     * @throws Throwable
     */
    private function query(string $type, array $filter = [], array $with = []): QueryBuilder
    {
        $categoryTypes = getCategoryType();

        if (!array_key_exists($type, $categoryTypes)) {
            throw new CategoryTypeNotMatchException($type);
        }

        $hierarchical = $categoryTypes[$type]['hierarchical'];

        if ($hierarchical) {
            $category_table = config('category.tables.category');
            $category_path_table = config('category.tables.category_path');
            $translation_table = config('translation.tables.translation');

            $fields = [
                'id',
                'type',
                'parent_id',
                'ordering',
                'status',
                'created_at',
                'updated_at'
            ];

            $query = QueryBuilder::for(CategoryPath::class)
                ->from($category_path_table . ' as cp')
                ->select(['c.*']);

            $category_name = Translation::query()
                ->select('value')
                ->whereColumn('translatable_id', 'c.id')
                ->where('translatable_type', CategoryModel::class)
                ->where('locale', app()->getLocale())
                ->where('key', 'name')
                ->getQuery();

            $query->selectSub($category_name, 'name');

            $query->selectSub("GROUP_CONCAT( `t`.`value` ORDER BY `cp`.`level` SEPARATOR '  »  ' )", "name_multiple");

            $query->join($category_table . ' as c', 'cp.category_id', '=', 'c.id');

            $query->join($translation_table. ' as t', function ($join) use ($category_table) {
                $join->on('t.translatable_id', '=', 'cp.path_id')
                    ->where('t.translatable_type', '=', CategoryModel::class)
                    ->where('t.locale', '=', app()->getLocale())
                    ->where('t.key', '=', 'name');
            });

            $query->where([
                'cp.type' => $type,
                'c.type' => $type,
            ]);

            $query->groupBy('cp.category_id');

            $query->allowedFields($fields)
                ->allowedSorts($fields)
                ->allowedFilters($fields)
                ->defaultSort([
                    '-created_at'
                ])
                ->where($filter);

            if (!empty($with)) {
                $query->with($with);
            }

            return $query;
        } else {
            $fields = [
                'id',
                'type',
                'name',
                'ordering',
                'status',
                'created_at',
                'updated_at'
            ];

            $query = QueryBuilder::for(CategoryModel::class);

            $query->select(['*']);
            $query->where('type', $type);

            $translation_name = Translation::query()
                ->select('value')
                ->whereColumn('translatable_id', config('category.tables.category') . '.id')
                ->where('translatable_type', CategoryModel::class)
                ->where('locale', app()->getLocale())
                ->where('key', 'name')
                ->getQuery();

            $query->selectSub($translation_name, 'name');

            $query->allowedFields($fields)
                ->allowedSorts($fields)
                ->allowedFilters($fields)
                ->defaultSort([
                    'type',
                    '-ordering',
                    '-created_at'
                ])
                ->where($filter);

            if (!empty($with)) {
                $query->with($with);
            }

            return $query;
        }
    }

    /**
     * Paginate the specified categories.
     *
     * @param string $type
     * @param array $filter
     * @param int $page_limit
     * @param array $with
     *
     * @return AnonymousResourceCollection
     * @throws Throwable
     */
    public function paginate(string $type, array $filter = [], int $page_limit = 15, array $with = []): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            $this->query($type, $filter, $with)->paginate($page_limit)
        );
    }

    /**
     * Get all categories.
     *
     * @param string $type
     * @param array $filter
     * @param array $with
     *
     * @return AnonymousResourceCollection
     * @throws Throwable
     */
    public function all(string $type, array $filter = [], array $with = []): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            $this->query($type, $filter, $with)->get()
        );
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

                $flag_name = false;
                foreach ($category_ids as $item) {
                    if ($this->hasUsed($item)) {
                        $flag_name = $this->getName($item);
                        break;
                    }
                }

                if ($flag_name) {
                    throw new CategoryUsedException($flag_name);
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
                    throw new CategoryUsedException($this->getName($category_id));
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

    /**
     * Get Name the specified category.
     *
     * @param int $category_id
     * @param bool $concat
     * @param string|null $locale
     *
     * @return string
     * @throws Throwable
     */
    public function getName(int $category_id, bool $concat = true, string $locale = null): string
    {
        /**
         * @var CategoryModel $category
         */
        $category = CategoryModel::find($category_id);

        if (!$category) {
            throw new CategoryNotFoundException($category_id);
        }

        $locale = $locale ?? app()->getLocale();

        $categoryTypes = getCategoryType();
        $hierarchical = $categoryTypes[$category->type]['hierarchical'];

        if ($hierarchical && $concat) {
            $names = [];
            $paths = CategoryPath::query()->select('path_id')->where([
                'category_id' => $category_id
            ])->orderBy('level')->get()->toArray();

            foreach ($paths as $path) {
                $names[] = Translation::query()->where([
                    'translatable_id' => $path['path_id'],
                    'translatable_type' => CategoryModel::class,
                    'locale' => $locale,
                    'key' => 'name'
                ])->value('value');
            }

            $char = config('category.arrow_icon.' . trans('domi::base.direction'));

            return implode($char, $names);
        } else {
            return Translation::query()->where([
                'translatable_id' => $category_id,
                'translatable_type' => CategoryModel::class,
                'locale' => $locale,
                'key' => 'name'
            ])->value('value');
        }
    }

    /**
     * Used In category
     *
     * @param int $category_id
     *
     * @return array
     * @throws Throwable
     */
    public function usedIn(int $category_id): array
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

        return [
            'ok' => true,
            'message' => trans('category::base.messages.used_in', [
                'count' => $category_relations->count()
            ]),
            'data' => CategoryRelationResource::collection($category_relations),
            'status' => 200
        ];
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
