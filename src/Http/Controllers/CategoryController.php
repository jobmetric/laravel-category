<?php

namespace JobMetric\Category\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JobMetric\Category\Facades\Category;
use JobMetric\Category\Http\Requests\StoreCategoryRequest;
use JobMetric\Category\Models\Category as CategoryModel;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Controllers\Controller;
use JobMetric\Panelio\Http\Requests\ActionListRequest;
use JobMetric\Panelio\Http\Requests\ExportActionListRequest;
use JobMetric\Panelio\Http\Requests\ImportActionListRequest;
use Throwable;

class CategoryController extends Controller
{
    private array $route;

    public function __construct()
    {
        if (request()->route()) {
            $parameters = request()->route()->parameters();

            $this->route = [
                'index' => route('category.{type}.index', $parameters),
                'create' => route('category.{type}.create', $parameters),
                'store' => route('category.{type}.store', $parameters),
                'options' => route('category.options', $parameters),
                'import' => route('category.import', $parameters),
                'export' => route('category.export', $parameters),
            ];
        }
    }

    /**
     * Display a listing of the category.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View|JsonResponse
     */
    public function index(string $panel, string $section, string $type): View|JsonResponse
    {
        if (request()->ajax()) {
            $query = Category::query($type, with: ['translations']);

            return Datatable::of($query);
        }

        // Set data category
        $data['name'] = getCategoryTypeArg($type);

        DomiTitle(getCategoryTypeArg($type));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name']);

        // add button
        Button::add($this->route['create']);
        Button::status();
        Button::import();
        Button::export();

        DomiLocalize('category', [
            'route' => $this->route['index'],
        ]);

        DomiScript('assets/vendor/category/js/list.js');

        $data['type'] = $type;

        $data['route'] = $this->route['options'];
        $data['import_action'] = $this->route['import'];
        $data['export_action'] = $this->route['export'];

        return view('category::list', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View
     */
    public function create(string $panel, string $section, string $type): View
    {
        // Set data category
        $data['name'] = getCategoryTypeArg($type);

        DomiTitle(trans('category::base.form.create.title', [
            'type' => $data['name']
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name'], $this->route['index']);
        Breadcrumb::add(trans('category::base.form.create.title', [
            'type' => $data['name']
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/category/js/form.js');

        $data['type'] = $type;
        $data['action'] = $this->route['store'];

        $data['description'] = getCategoryTypeArg($type, 'description');
        $data['hierarchical'] = getCategoryTypeArg($type, 'hierarchical');
        $data['translation'] = getCategoryTypeArg($type, 'translation');
        $data['metadata'] = getCategoryTypeArg($type, 'metadata');
        $data['has_url'] = getCategoryTypeArg($type, 'has_url');
        $data['has_base_media'] = getCategoryTypeArg($type, 'has_base_media');
        $data['media'] = getCategoryTypeArg($type, 'media');

        $data['categories'] = Category::all($type);

        return view('category::form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCategoryRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreCategoryRequest $request, string $panel, string $section, string $type): RedirectResponse
    {
        $form_data = $request->all();

        $category = Category::store($request->validated());

        if ($category['ok']) {
            $this->alert($category['message']);

            if($form_data['save'] == 'save.new') {
                return back();
            }

            if($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('category.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_category' => $category['data']->id
            ]);
        }

        $this->alert($category['message'], 'danger');

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $panel, string $section, string $type, CategoryModel $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $panel, string $section, string $type, CategoryModel $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $panel, string $section, string $type, CategoryModel $category)
    {
        //
    }

    /**
     * Run Actions in list
     */
    public function options(ActionListRequest $request, string $panel, string $section, string $type)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        $alert = null;
        switch ($action) {
            case 'status.enable':
                foreach ($ids as $id) {
                    Category::update($id, ['status' => true]);
                }
                $alert = trans('panelio::base.message.status.enable', ['count' => count($ids)]);
                break;
            case 'status.disable':
                foreach ($ids as $id) {
                    Category::update($id, ['status' => false]);
                }
                $alert = trans('panelio::base.message.status.disable', ['count' => count($ids)]);
                break;
        }

        return back()->with('success', $alert);
    }

    /**
     * Import data
     */
    public function import(ImportActionListRequest $request, string $panel, string $section, string $type)
    {
        //
    }

    /**
     * Export data
     */
    public function export(ExportActionListRequest $request, string $panel, string $section, string $type)
    {
        $export_type = $request->type;

        $filePath = public_path('favicon.ico');
        $fileName = 'favicon.ico';

        return response()->download($filePath, $fileName, [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }
}
