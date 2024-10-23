<?php

namespace JobMetric\Category\Http\Controllers;

use Illuminate\Http\Request;
use JobMetric\Category\Facades\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $panel, string $section, string $type)
    {
        $include_data = $request->input('include_data');

        if ($include_data) {
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

        return view('category::list', [
            'type' => $type,
        ]);
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
