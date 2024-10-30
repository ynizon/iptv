<?php

namespace App\View\Components;

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
        $categories = [];
        $urls = DB::table('urls')->select('category')->where("filter","=",0)
            ->distinct()->orderBy("category")->get();
        foreach ($urls as $url)
        {
            $categories[] = $url->category;
        }
        return view('components.categories', compact('categories'));
    }
}
