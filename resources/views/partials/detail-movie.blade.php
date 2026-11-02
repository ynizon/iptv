{{-- Detail panel of a movie, loaded when its card is selected --}}
@php($tvLink = config("app.url") . "/tv/" . Auth::user()->tvToken())
@php($parsed = $url->parsedName())
@php($rating = $url->rating())
<div class="cat-detail-item" data-detail="{{$url->id}}" hidden>
    <div class="cat-detail-head">
        <img loading="lazy" src="{{($url->picture != '') ? "/picture/".$url->id : '/images/default.webp'}}" alt="">
        <div>
            <h2 id="urlname-{{$url->id}}">{{$parsed['title']}}</h2>
            <p class="cat-detail-meta">{{$parsed['year'] ?? ($url->year ?: '')}}</p>
            <div class="cat-tags">
                @foreach ($parsed['tags'] as $tag)
                    <span>{{$tag}}</span>
                @endforeach
            </div>
        </div>
    </div>
    <div class="cat-score">
        <span class="cat-badge cat-badge-big tier-{{$rating['tier']}}" title="{{$rating['tooltip']}}">{{$rating['badge']}}</span>
        <div>
            <strong>{{$rating['label']}}</strong>
            <span>{{$rating['line']}}</span>
        </div>
    </div>
    @if ($url->isDoubtful())
        <div class="cat-warning" role="note">{{__("TMDB data to check: the title says :title, the record found is from :tmdb. The rating and the summary may belong to another title.", ['title' => $parsed['year'], 'tmdb' => $url->year])}}</div>
    @endif
    <h3>{{__("Summary")}}</h3>
    <p class="cat-overview">{{($url->imdb != '' && $url->imdb != '-') ? $url->imdb : __("No summary yet: it will be fetched by the next refresh:info run.")}}</p>
    <div class="cat-actions">
        <a id="ahref-{{$url->id}}" href="iptv://{{$url->urlFinal}}#{{$url->counterSec(Auth::id())}}" data-id="{{$url->id}}" class="stream cat-act cat-act-primary">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-play"/></svg>
            {{__("Play")}}
            <span class="counter" data-min="{{$url->counterMin(Auth::id())}}" id="counter-{{$url->id}}">{{$url->counter(Auth::id())}}</span>
        </a>
        <button type="button" class="cat-act @if ($url->isFavorite(Auth::id())) active @endif" data-action="favorite" onclick="addFavorite(this, {{$url->id}}); catalogSync({{$url->id}})">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-heart"/></svg>
            {{__("Favorite")}}
        </button>
        <button type="button" id="eye-{{$url->id}}" class="cat-act @if ($url->isWatched(Auth::id())) active @endif" data-action="seen" onclick="addWatched(this, {{$url->id}}); catalogSync({{$url->id}})">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-eye"/></svg>
            {{__("Seen")}}
        </button>
    </div>
    <div class="cat-actions">
        <button type="button" class="cat-act" onclick="copyLink(@js($url->urlFinal))">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-copy"/></svg>
            {{__("Copy link")}}
        </button>
        <button type="button" class="cat-act" title="{{$tvLink}}" onclick="addTvStream(this, @js($url->url), @js($tvLink))">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-tv"/></svg>
            {{__("Send to TV")}}
        </button>
        <button type="button" class="cat-act" onclick="catalogRemove({{$url->id}})" title="{{__("Remove from favorites and history")}}">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-close"/></svg>
            {{__("Remove")}}
        </button>
    </div>
    @if (count($versions ?? []))
        {{-- The same movie in another resolution or language --}}
        <details class="cat-season" open>
            <summary>{{__("Other versions")}} <span>{{count($versions)}}</span></summary>
            <ul>
                @foreach ($versions as $version)
                    <li>
                        <a id="ahref-{{$version->id}}" href="iptv://{{$version->urlFinal}}#{{$version->counterSec(Auth::id())}}" data-id="{{$version->id}}" class="stream cat-episode" title="{{$version->name}}">
                            <svg width="14" height="14" aria-hidden="true"><use href="#ci-play"/></svg>
                            {{implode(' · ', $version->parsedName()['tags']) ?: $version->name}}
                        </a>
                        <span id="urlname-{{$version->id}}" hidden>{{$version->name}}</span>
                        <span class="counter" data-min="{{$version->counterMin(Auth::id())}}" id="counter-{{$version->id}}">{{$version->counter(Auth::id())}}</span>
                        <button type="button" class="cat-icon" onclick="copyLink(@js($version->urlFinal))" aria-label="{{__("Copy link")}}">
                            <svg width="16" height="16" aria-hidden="true"><use href="#ci-copy"/></svg>
                        </button>
                        <button type="button" class="cat-icon" title="{{$tvLink}}" onclick="addTvStream(this, @js($version->url), @js($tvLink))" aria-label="{{__("Send to TV")}}">
                            <svg width="16" height="16" aria-hidden="true"><use href="#ci-tv"/></svg>
                        </button>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif
</div>
