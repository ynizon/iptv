@foreach($warnings as $warning)
    <div class="alert alert-warning" role="alert">
        <i class="fa fa-warning"></i>&nbsp; {{$warning['from'] . ' (- ' . $warning['counter'] . ') : ' . $warning['user'] . __(" is watching ") . $warning['name']}}
    </div>
@endforeach
<ul class="nav nav-tabs">
    @if (count($urls["movies"]) > 0)
        <li class="nav-item">
            <button class="nav-link" id="movies-tab" data-bs-toggle="tab" data-bs-target="#movies" type="button"
                    role="tab" aria-controls="movies" aria-selected="true">{{__("Movies")}} ({{count($urls["movies"])}})</button>
        </li>
    @endif
    @if (count($urls["series"]) > 0)
        <li class="nav-item">
            <button class="nav-link" id="series-tab" data-bs-toggle="tab" data-bs-target="#series" type="button"
                    role="tab" aria-controls="series" aria-selected="true">{{__("Series")}} ({{count($urls["series"])}})</button>
        </li>
    @endif
    @if (count($urls["channels"]) > 0)
        <li class="nav-item">
            <button class="nav-link" id="tv-tab" data-bs-toggle="tab" data-bs-target="#tv" type="button" role="tab"
                    aria-controls="tv" aria-selected="true">{{__("Channels")}} ({{count($urls["channels"])}})</button>
        </li>
    @endif
</ul>

<div class="tab-content" id="myTabContent">
    @if (count($urls["movies"]) > 0)
        <div class="tab-pane fade" id="movies" role="tabpanel" aria-labelledby="movies-tab">
            <div class="row">
                @foreach ($urls["movies"] as $url)
                    <div style="float:left;width:250px;" class="col-md-2 mb-4 mb-lg-0">
                        <div>
                            <div style="float:left;width:20px;">
                                <br/>
                                <i id="eye-{{$url->id}}" class="fa fa-eye @if ($url->isWatched(1)) active @endif" style="cursor: pointer" onclick="addWatched(this, {{$url->id}})"></i>
                                <br/>
                                <i class="fa fa-heart @if ($url->isFavorite(1)) active @endif" style="cursor: pointer" onclick="addFavorite(this, {{$url->id}})"></i>
                                <br/>
                                <i class="fa fa-remove" style="cursor: pointer" onclick="remove(this, {{$url->id}})"></i>
                                <br/>
                                <i class="fa fa-copy" style="cursor: pointer" onclick="copyLink('{{$url->urlFinal}}')"></i>
                                <br/>
                                @if ($url->note != '' && $url->note != 'N/A' && $url->note != '-1')
                                    <br/>
                                    {{round($url->note,2)}}
                                @endif
                                
                                <div class="counter" data-min="{{$url->counterMin(Auth::user()->id)}}" id="counter-{{$url->id}}">{{$url->counter(Auth::user()->id)}}</div>
                            </div>
                            <div class="movie">
                                <a
                                    id="ahref-{{$url->id}}" href="iptv://{{ $url->urlFinal }}#{{$url->counterSec(Auth::user()->id)}}" data-id="{{$url->id}}" class="stream">
                                    <img src="{{($url->picture != '') ? "/picture/".$url->id : '/images/default.webp'}}" />
                                    <br/>
                                    <span id="urlname-{{$url->id}}">{{$url->name}}</span>
                                </a>
                                <div onclick="showDescription({{$url->id}}, 1)" style="text-align: center">
                                    {{__("Show description")}}
                                </div>
                                <div onmouseover="showDescription({{$url->id}}, 1)" onmouseout="showDescription({{$url->id}}, 0)" onclick="showDescription({{$url->id}}, 0)"
                                     id="description-{{$url->id}}" style="display:none" class="popin">
                                    <h4>{{$url->name}}</h4>
                                    <hr/>
                                    @if ($url->note>0)
                                        <h5>{{round($url->note,2)}}/10 ({{$url->votes}} votes)</h5>
                                        <hr/>
                                    @endif
                                    {{$url->imdb}}
                                </div>
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
                    <div style="clear:both;">
                        <div style="float:left;width:100%;padding-bottom:20px;">
                            <div>
                                <div style="float:left;padding-left:15px;">
                                    @if (isset($seasons['01']['01']))
                                        <i class="fa fa-heart @if ($seasons['01']['01']->isFavorite(1)) active @endif"
                                           style="cursor: pointer" onclick="addFavoriteSerie(this, '{{$serie}}')"></i>
                                        <br/>
                                        <i class="fa fa-remove" style="cursor: pointer" onclick="removeSerie(this, '{{$serie}}')"></i>
                                    @endif
                                </div>
                                <div style="float:left;padding-left:15px;">
                                    <img class="serie-pic" style="max-width:150px" src="{{($pictures[$serie] != '') ? "/picture/".$pictures[$serie] : '/images/default.webp'}}" />
                                </div>
                                <div style="float:left;padding-left:15px;">
                                    <h5>{{$serie}}</h5>
                                    {{$descriptions[$serie]}}
                                </div>
                                <div style="clear:both;padding-top:5px;" >
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
                                                                        <a id="ahref-{{$url->id}}" href="iptv://{{$url->urlFinal}}#{{$url->counterSec(Auth::user()->id)}}" data-id="{{$url->id}}" class="stream left" style="display:inline; ">
                                                                            Episode {{$episod}}
                                                                        </a>
                                                                        <span id="urlname-{{$url->id}}">{{$url->name}}</span>
                                                                        <span class="counter" data-min="{{$url->counterMin(Auth::user()->id)}}" id="counter-{{$url->id}}">{{$url->counter(Auth::user()->id)}}</span>
                                                                        <i id="eye-{{$url->id}}" class="fa fa-eye @if ($url->isWatched(1)) active @endif" style="cursor: pointer" onclick="addWatched(this, {{$url->id}})"></i>
                                                                        &nbsp;&nbsp;
                                                                        <i class="fa fa-copy" style="cursor: pointer" onclick="copyLink('{{$url->urlFinal}}')"></i>
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
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    @if (count($urls["channels"]) > 0)
        <div class="tab-pane fade" id="tv" role="tabpanel" aria-labelledby="tv-tab">
            <div class="row">
                @foreach ($urls["channels"] as $url)
                    <div style="float:left;width:250px;" class="col-md-2 mb-4 mb-lg-0">
                        <div style="clear:both">
                            <div style="float:left;width:20px;">
                                <br/>
                                <i class="fa fa-heart @if ($url->isFavorite(1)) active @endif" style="cursor: pointer" onclick="addFavorite(this, {{$url->id}})"></i>
                                <br/>
                                <i class="fa fa-copy" style="cursor: pointer" onclick="copyLink('{{$url->urlFinal}}')"></i>
                            </div>
                            <div class="movie">
                                <a href="iptv://{{$url->urlFinal}}" data-id="{{$url->id}}">
                                    <img class="channel-pic" src="{{($url->picture != '') ? "/picture/".$url->id : '/images/default.webp'}}" />
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
</div>

<script>
    $( ".stream" ).on( "click", function(button) {
        addCounter($(this).attr("data-id"))
    });
    $(".nav-link").removeClass("active");
    $(".nav-link:first").addClass("active");
    $(".tab-pane").removeClass("active");
    $(".tab-pane:first").addClass("active");
    $(".tab-pane:first").addClass("show");
    
    function copyLink(url){
        navigator.clipboard.writeText(url);
    }
</script>
