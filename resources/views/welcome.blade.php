<x-app>
    <div class="col-md-2">
        <ul>
            <li><a href="/playlists" style="display:inline"><i class="fa fa-settings"></i> PLAYLISTS</a> -
                <a href="/filters"  style="display:inline">FILTRES</a></li>
            <li class="category" style="cursor: pointer"
                data-category="-1"><i class="fa fa-heart"></i> FAVORIS</li>
            @foreach ($categories as $category)
                <li class="category" style="cursor: pointer"
                    data-category="{{$category}}">@if ($category == '') TOUS @else {{$category}} @endif</li>
            @endforeach
        </ul>
    </div>
    <div class="col-md-10">
        <div class="row mt-5">
            <div class="col-md-5 mx-auto">
                <div class="input-group">
                    @foreach (\App\Http\Controllers\SearchController::FILTERS as $format => $checked)
                        <div class="form-check px-5">
                            <input class="form-check-input formats" {{$checked}}
                            name="format[]" type="checkbox" id="chk{{$format}}" value="{{$format}}">
                            <label class="form-check-label" for="chk{{$format}}">{{$format}}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-md-5 mx-auto">
                    <input class="form-control border rounded-pill" type="search" placeholder="recherche"
                           id="search" minlength="3">
                    <input id="category" value="" type="hidden"/>
                </div>
            </div>

            <div id="list">
            </div>
        </div>
    </div>
</x-app>
