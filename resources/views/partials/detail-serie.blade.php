{{-- Detail panel of a series with its episodes, loaded when its card is selected --}}
@php($tvLink = config("app.url") . "/tv/" . Auth::user()->tvToken())
@php($parsed = $first->parsedName())
@php($rating = $first->rating())
@php(ksort($seasons))
<div class="cat-detail-item" data-detail="s{{$first->id}}" hidden>
    <div class="cat-detail-head">
        <img loading="lazy" src="{{($first->picture != '') ? "/picture/".$first->id : '/images/default.webp'}}" alt="">
        <div>
            <h2>{{$parsed['title']}}</h2>
            <p class="cat-detail-meta">{{implode(' · ', array_filter([$first->year ?: null, trans_choice(':count season|:count seasons', count($seasons))]))}}</p>
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
    <h3>{{__("Summary")}}</h3>
    <p class="cat-overview">{{($first->imdb != '' && $first->imdb != '-') ? $first->imdb : __("No summary yet: it will be fetched by the next refresh:info run.")}}</p>
    <div class="cat-actions">
        <button type="button" class="cat-act @if ($first->isFavorite(Auth::id())) active @endif" data-action="favorite" onclick="addFavoriteSerie(this, @js($serie)); catalogSync('s{{$first->id}}')">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-heart"/></svg>
            {{__("Favorite")}}
        </button>
        <button type="button" class="cat-act" onclick="catalogRemoveSerie(@js($serie), 's{{$first->id}}')" title="{{__("Remove from favorites and history")}}">
            <svg width="18" height="18" aria-hidden="true"><use href="#ci-close"/></svg>
            {{__("Remove")}}
        </button>
    </div>
    @foreach ($seasons as $season => $episods)
        @php(ksort($episods))
        <details class="cat-season" @if ($loop->first) open @endif>
            <summary>{{__("Season")}} {{$season}} <span>{{trans_choice(':count ep.|:count ep.', count($episods))}}</span></summary>
            <ul>
                @foreach ($episods as $episod => $url)
                    <li>
                        <a id="ahref-{{$url->id}}" href="iptv://{{$url->urlFinal}}#{{$url->counterSec(Auth::id())}}" data-id="{{$url->id}}" class="stream cat-episode">
                            <svg width="14" height="14" aria-hidden="true"><use href="#ci-play"/></svg>
                            {{__("Episode")}} {{$episod}}
                        </a>
                        <span id="urlname-{{$url->id}}" hidden>{{$url->name}}</span>
                        <span class="counter" data-min="{{$url->counterMin(Auth::id())}}" id="counter-{{$url->id}}">{{$url->counter(Auth::id())}}</span>
                        <button type="button" id="eye-{{$url->id}}" class="cat-icon @if ($url->isWatched(Auth::id())) active @endif" onclick="addWatched(this, {{$url->id}})" aria-label="{{__("Seen")}}">
                            <svg width="16" height="16" aria-hidden="true"><use href="#ci-eye"/></svg>
                        </button>
                        <button type="button" class="cat-icon" onclick="copyLink(@js($url->urlFinal))" aria-label="{{__("Copy link")}}">
                            <svg width="16" height="16" aria-hidden="true"><use href="#ci-copy"/></svg>
                        </button>
                        <button type="button" class="cat-icon" title="{{$tvLink}}" onclick="addTvStream(this, @js($url->url), @js($tvLink))" aria-label="{{__("Send to TV")}}">
                            <svg width="16" height="16" aria-hidden="true"><use href="#ci-tv"/></svg>
                        </button>
                    </li>
                @endforeach
            </ul>
        </details>
    @endforeach
</div>
