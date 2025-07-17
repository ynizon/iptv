<?php

namespace App\View\Components;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Categories extends Component
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $renames = Category::all();
        $categories = [];
        $originalCategories = [];
        $urls = DB::table('urls')->select('category')->where("filter","=",0)
            ->distinct()->orderBy("category")->get();
        foreach ($urls as $url)
        {
            $category = trim($url->category);
            foreach ($renames as $rename)
            {
                $category = str_replace($rename->from, $rename->to, $category);
                $category = str_replace("|", "-", $category);
            }

            $category = trim($category);
            if (!in_array($category, $categories))
            {
                $categories[] = $category;
                $originalCategories[$category] = $url->category;
            }
        }
        return view('components.categories', compact('categories', 'originalCategories'));
    }
}
