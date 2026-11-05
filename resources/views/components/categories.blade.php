<li style="cursor: pointer" class="category"
    data-category="-1"><i class="fa fa-heart"></i>
    <span >{{__("Favorites")}}</span></li>
<li id="recent" class="category" style="cursor: pointer"
    data-category="-2"><i class="fa fa-eye"></i>
    <span >{{__("Recent")}}</span></li>
<li style="cursor: pointer" class="category"
    data-category=""> <i class="fa fa-star"></i> <span >{{__("Tous")}}</span></li>
<li style="cursor: pointer" class="category" title="{{__("The main French channels")}}"
    data-category="{{\App\Models\Category::MAIN_TV}}"> <i class="fa fa-tv"></i> <span >{{__("TV")}}</span></li>
<li style="cursor: pointer" class="category" title="{{__("The last movies and series available")}}"
    data-category="{{\App\Models\Category::NEWS}}"> <i class="fa fa-bolt"></i> <span >{{__("News")}}</span></li>
<li><br/></li>
@foreach ($categories as $category)
    @if ($category != '')
        {{-- Long names are cut on one line: the full name on hover --}}
        <li style="cursor: pointer" class="category" title="{{$category}}"
            data-category="{{$category}}"><i class="fa fa-list m3u"></i>
            <span class="category-name">{{$category}}</span></li>
    @endif
@endforeach
