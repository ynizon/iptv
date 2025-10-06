<li style="cursor: pointer" class="category"
    data-category="-1"><i class="fa fa-heart"></i>
    <span >{{__("Favorites")}}</span></li>
<li id="recent" class="category" style="cursor: pointer"
    data-category="-2"><i class="fa fa-eye"></i>
    <span >{{__("Recent")}}</span></li>
<li style="cursor: pointer" class="category"
    data-category=""> <i class="fa fa-star"></i> <span >{{__("Tous")}}</span></li>
<li><br/></li>
@foreach ($categories as $category)
    @if ($category != '')
        <li style="cursor: pointer" class="category"
            data-category="{{$originalCategories[$category]}}"><i class="fa fa-list m3u"></i> &nbsp;
            <span>{{$category}}</span></li>
    @endif
@endforeach
