<li class="category" style="cursor: pointer"
    data-category="-1"><i class="fa fa-heart"></i> FAVORIS</li>
<li class="category" style="cursor: pointer"
    data-category=""> - TOUS - </li>
@foreach ($categories as $category)
    @if ($category != '')
        <li class="category" style="cursor: pointer"
            data-category="{{$category}}">{{$category}}</li>
    @endif
@endforeach
