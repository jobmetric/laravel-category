<?php

namespace JobMetric\Category\Http\Controllers;

use Illuminate\Http\Request;
use JobMetric\Category\Facades\Category;
use JobMetric\Category\Models\Category as CategoryModel;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Requests\ActionListRequest;
use JobMetric\Panelio\Http\Requests\ExportActionListRequest;
use JobMetric\Panelio\Http\Requests\ImportActionListRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $panel, string $section, string $type)
    {
        if (request()->ajax()) {
            $query = Category::query($type);

            return Datatable::of($query);
        }

        // Set data category
        $data['name'] = getCategoryTypeArg($type);

        DomiTitle(getCategoryTypeArg($type));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['name']);

        // add button
        Button::add(route('category.{type}.create', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
        ]));
        Button::status();

        DomiLocalize('category', [
            'route' => route('category.{type}.index', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
            ]),
        ]);

        DomiScript('assets/vendor/category/js/list.js');

        $data['type'] = $type;

        $data['route'] = route('category.options', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type
        ]);

        $data['import_action'] = route('category.import', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type
        ]);

        $data['export_action'] = route('category.export', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type
        ]);

        return view('category::list', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $panel, string $section, string $type)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $panel, string $section, string $type)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $panel, string $section, string $type, CategoryModel $category)
    {
        //
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
