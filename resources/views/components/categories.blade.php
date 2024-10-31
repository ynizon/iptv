<li class="category" style="cursor: pointer"
    data-category="-1"><i class="fa fa-heart"></i> {{__("Favorites")}}</li>
<li id="recent" class="category" style="cursor: pointer"
    data-category="-2"><i class="fa fa-eye"></i> {{__("Recent")}}</li>
<li class="category" style="cursor: pointer"
    data-category=""> - {{__("Tous")}} - </li>
@foreach ($categories as $category)
    @if ($category != '')
        <li class="category" style="cursor: pointer"
            data-category="{{$category}}">{{$category}}</li>
    @endif
@endforeach
