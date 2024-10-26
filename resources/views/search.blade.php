<ul class="nav nav-tabs">
    @if (count($urls["movies"]) > 0)
        <li class="nav-item">
            <button class="nav-link" id="movies-tab" data-bs-toggle="tab" data-bs-target="#movies" type="button"
                    role="tab" aria-controls="movies" aria-selected="true">Films ({{count($urls["movies"])}})</button>
        </li>
    @endif
    @if (count($urls["series"]) > 0)
        <li class="nav-item">
            <button class="nav-link" id="series-tab" data-bs-toggle="tab" data-bs-target="#series" type="button"
                    role="tab" aria-controls="series" aria-selected="true">Séries ({{count($urls["series"])}})</button>
        </li>
    @endif
    @if (count($urls["channels"]) > 0)
        <li class="nav-item">
            <button class="nav-link" id="tv-tab" data-bs-toggle="tab" data-bs-target="#tv" type="button" role="tab"
                    aria-controls="tv" aria-selected="true">TV ({{count($urls["channels"])}})</button>
        </li>
    @endif
</ul>

<div class="tab-content" id="myTabContent">
    @if (count($urls["movies"]) > 0)
        <div class="tab-pane fade" id="movies" role="tabpanel" aria-labelledby="movies-tab">
            <div class="row mt-8">
                @foreach ($urls["movies"] as $url)
                    <div class="col-md-2 mb-4 mb-lg-0">
                        <div style="clear:both">
                            <div style="float:left;width:20px;">
                                <br/>
                                <i class="fa fa-eye @if ($url->watched) active @endif" style="cursor: pointer" onclick="addWatched(this, {{$url->id}})"></i>
                                <br/>
                                <i class="fa fa-heart @if ($url->favorite) active @endif" style="cursor: pointer" onclick="addFavorite(this, {{$url->id}})"></i>
                            </div>
                            <div style="float:left;width:200px;">
                                <a href="#" data-href="/view/{{$url->id}}" class="stream">
                                    <img src="{{($url->picture != '') ? $url->picture : '/images/default.webp'}}" />
                                    <br/>
                                    {{$url->name}}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if (count($urls["series"]) > 0)
        <div class="tab-pane fade" id="series" role="tabpanel" aria-labelledby="series-tab">
            @foreach ($urls["series"] as $serie => $seasons)
                <div class="col-lg-12 col-md-12 mb-4 mb-lg-0 py-2">
                    <h5>{{$serie}}
                        @if (isset($seasons['01']['01']))
                            <i class="fa fa-heart @if ($seasons['01']['01']->favorite) active @endif"
                               style="cursor: pointer" onclick="addFavoriteSerie(this, '{{$serie}}')"></i>
                        @endif
                    </h5>
                    <div class="accordion" id="accordion{{md5($serie)}}">
                        @foreach ($seasons as $season => $episods)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{md5($serie)}}{{ $loop->index }}" aria-expanded="true"
                                            aria-controls="collapse{{md5($serie)}}{{ $loop->index }}">
                                        Saison {{$season}}
                                    </button>
                                </h2>
                                <div id="collapse{{md5($serie)}}{{ $loop->index }}" class="accordion-collapse collapse"
                                     aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#accordion{{md5($serie)}}">
                                    <div class="accordion-body">
                                        <ul>
                                            @foreach ($episods as $episod => $url)
                                                <li>
                                                    <div>
                                                        <a href="#" data-href="/view/{{$url->id}}" class="stream left" style="display:inline; ">
                                                            Episode {{$episod}}
                                                        </a>
                                                        <i class="fa fa-eye @if ($url->watched) active @endif" style="cursor: pointer" onclick="addWatched(this, {{$url->id}})"></i>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @if (count($urls["channels"]) > 0)
        <div class="tab-pane fade" id="tv" role="tabpanel" aria-labelledby="tv-tab">
            @foreach ($urls["channels"] as $url)
                <div class="col-lg-4 col-md-12 mb-4 mb-lg-0">
                    <div style="clear:both">
                        <div style="float:left;width:20px;">
                            <br/>
                            <i class="fa fa-eye @if ($url->watched) active @endif" style="cursor: pointer" onclick="addWatched(this, {{$url->id}})"></i>
                            <br/>
                            <i class="fa fa-heart @if ($url->favorite) active @endif" style="cursor: pointer" onclick="addFavorite(this, {{$url->id}})"></i>
                        </div>
                        <div style="float:left;width:200px;">
                            <a href="#" data-href="/view/{{$url->id}}" class="stream">
                                <img src="{{($url->picture != '') ? $url->picture : '/images/default.webp'}}" />
                                <br/>
                                {{$url->name}}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    $( ".stream" ).on( "click", function() {
        $.ajax({
            type: "GET",
            url: $(this).attr("data-href")
        })
    });
    $(".nav-link").removeClass("active");
    $(".nav-link:first").addClass("active");
    $(".tab-pane").removeClass("active");
    $(".tab-pane:first").addClass("active");
    $(".tab-pane:first").addClass("show");
</script>
