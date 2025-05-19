<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy("from")->get();
        return view("categories/index", compact("categories"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = new Category();
        return view("categories/edit", compact("category"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = [];
        $validated['from'] = $request->input('from');
        $validated['to'] = $request->input('to');
        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success',__('Category created successfully.'));

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
        $category = Category::find($id);
        return view("categories/edit", compact("category"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = [];
        $validated['from'] = $request->input('from');
        $validated['to'] = $request->input('to');

        $category->update($validated);
        return redirect()->route('categories.index')
            ->with('success', __('Category updated successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', __('Category deleted successfully.'));
    }
}
