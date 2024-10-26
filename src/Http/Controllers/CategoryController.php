<?php

namespace JobMetric\Category\Http\Controllers;

use Illuminate\Http\Request;
use JobMetric\Category\Facades\Category;
use JobMetric\Domi\Facades\Domi;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Panelio;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $panel, string $section, string $type)
    {
        if (request()->ajax()) {
            $page_limit = $request->input('page_limit', 50);
            $with = $request->input('with', []);

            if ($request->has('filter.parent_id') && $request->input('filter.parent_id') === 'null') {
                $request->merge(['filter' => ['parent_id' => null]]);
            }

            $filter = $request->input('filter', []);

            if ($page_limit == -1) {
                $category = Category::all($type, $filter, $with);
            } else {
                $category = Category::paginate($type, $filter, $page_limit, $with);
            }

            return $this->responseCollection($category);
        }

        DomiTitle(getCategoryTypeArg($type));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add(getCategoryTypeArg($type));

        // add button
        Button::add(route('category.{type}.create', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
        ]));
        Button::status();

        DomiPlugins('datatable');

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
