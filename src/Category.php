<?php

namespace JobMetric\Category;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use JobMetric\Category\Http\Requests\StoreCategoryRequest;
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
     * Store a newly created resource in storage.
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

        return [
            'ok' => true,
            'message' => trans('category::base.messages.created'),
            'data' => $category
        ];
    }
}
