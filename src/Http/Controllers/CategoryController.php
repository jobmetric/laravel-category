<?php

namespace JobMetric\Category\Http\Controllers;

use Illuminate\Http\Request;
use JobMetric\Category\Facades\Category;
use JobMetric\Domi\Facades\Domi;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Facades\Panelio;

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

//        DomiScript('assets/vendor/category/js/list.js');

        DomiLocalize('category', [
            'route' => route('category.{type}.index', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
            ]),
        ]);

        DomiLocalize('language', [
            'button' => [
                'edit' => trans('panelio::base.button.edit'),
                'delete' => trans('panelio::base.button.delete'),
            ],
        ]);

        DomiScript('assets/vendor/category/js/list.js');

        $data['type'] = $type;

        return view('category::list', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
