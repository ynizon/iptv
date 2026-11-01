<?php

namespace App\View\Components;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Categories extends Component
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // The "recently added" categories first, then the alphabetical order
        $categories = collect(array_keys(Category::groups()))
            ->sortBy(fn ($category) => Category::isRecentlyAdded($category) ? 0 : 1)
            ->values()->all();
        return view('components.categories', compact('categories'));
    }
}
