<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\Url;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = Filter::orderBy("name")->get();
        return view("filters/index", compact("filters"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filter = new Filter();
        return view("filters/edit", compact("filter"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = [];
        $validated['name'] = $request->input('name');
        Filter::create($validated);
        Url::applyFilters();

        return redirect()->route('filters.index')
            ->with('success',__('Filter created successfully.'));

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
        $filter = Filter::find($id);
        return view("filters/edit", compact("filter"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Filter $filter)
    {
        $validated = [];
        $validated['name'] = $request->input('name');
        $filter->update($validated);
        Url::applyFilters();
        return redirect()->route('filters.index')
            ->with('success', __('Filter updated successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Filter $filter)
    {
        $filter->delete();
        Url::applyFilters();
        return redirect()->route('filters.index')
            ->with('success', __('Filter deleted successfully.'));
    }
}
