<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Models\VisibleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisibleCategoryController extends Controller
{
    /**
     * List every playlist category with its visibility
     */
    public function index()
    {
        $categories = DB::table('urls')->select('category', DB::raw('count(*) as nb'))
            ->groupBy('category')->orderBy('category')->get();
        $visibles = VisibleCategory::pluck('name')->flip();
        return view("visible_categories/index", compact("categories", "visibles"));
    }

    /**
     * Save the checked categories as the new whitelist
     */
    public function store(Request $request)
    {
        $names = array_unique($request->input('categories', []));
        DB::transaction(function () use ($names) {
            VisibleCategory::query()->delete();
            foreach ($names as $name) {
                VisibleCategory::create(['name' => (string) $name]);
            }
        });
        Url::applyFilters();

        return redirect()->route('visible-categories.index')
            ->with('success', __('Visible categories updated successfully.'));
    }
}
