@include('partials.warnings')
@php($tvLink = config("app.url") . "/tv/" . Auth::user()->tvToken())
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <symbol id="ci-star" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></g></symbol>
        <symbol id="ci-heart-fill" viewBox="0 0 24 24"><g fill="currentColor"><path d="M12 21s-7.5-4.6-9.5-9.3C1.2 8.4 3.4 5 6.8 5c2 0 3.4 1.1 4.2 2.4C11.8 6.1 13.2 5 15.2 5c3.4 0 5.6 3.4 4.3 6.7C17.5 16.4 12 21 12 21z"/></g></symbol>
        <symbol id="ci-play" viewBox="0 0 24 24"><g fill="currentColor"><path d="M7 4.5v15l12-7.5z"/></g></symbol>
        <symbol id="ci-heart" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 21s-7.5-4.6-9.5-9.3C1.2 8.4 3.4 5 6.8 5c2 0 3.4 1.1 4.2 2.4C11.8 6.1 13.2 5 15.2 5c3.4 0 5.6 3.4 4.3 6.7C17.5 16.4 12 21 12 21z"/></g></symbol>
        <symbol id="ci-eye" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></g></symbol>
        <symbol id="ci-copy" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></g></symbol>
        <symbol id="ci-tv" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="13" rx="2"/><path d="M8 22h8"/></g></symbol>
        <symbol id="ci-close" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></g></symbol>
    </defs>
</svg>
<div class="catalog" id="catalog" data-recent="{{ request('category') == -2 ? 1 : 0 }}" data-labels="{{ json_encode(['one' => __(':count title'), 'many' => __(':count titles'), 'bestFirst' => __(', best rated first'), 'empty' => __('No title matches. Try without « Good ratings only ».')]) }}">
    <div class="cat-tabs" role="tablist" aria-label="{{__("Content type")}}">
        @if (count($urls["movies"]) > 0)
            <button type="button" class="cat-tab" role="tab" data-tab="movies">{{__("Movies")}} <span>{{count($urls["movies"])}}</span></button>
        @endif
        @if (count($urls["series"]) > 0)
            <button type="button" class="cat-tab" role="tab" data-tab="series">{{__("Series")}} <span>{{count($urls["series"])}}</span></button>
        @endif
        @if (count($urls["channels"]) > 0)
            <button type="button" class="cat-tab" role="tab" data-tab="channels">{{__("Channels")}} <span>{{count($urls["channels"])}}</span></button>
        @endif
    </div>

    @if (count($urls["movies"]) + count($urls["series"]) + count($urls["channels"]) == 0)
        <div class="cat-empty">{{__("No result.")}}</div>
    @endif

    <div class="cat-tools" data-rated-tools>
        <span class="cat-tools-label">{{__("Sort")}}</span>
        @if (request('category') == -2)
            <button type="button" class="cat-chip" data-sort="recent">{{__("Last watched")}}</button>
        @endif
        <button type="button" class="cat-chip" data-sort="score">{{__("Best rated")}}</button>
        <button type="button" class="cat-chip" data-sort="year">{{__("Most recent")}}</button>
        <button type="button" class="cat-chip" data-sort="az">{{__("A → Z")}}</button>
        <span class="cat-tools-sep"></span>
        <button type="button" class="cat-chip" data-filter="good">
            <svg width="16" height="16" aria-hidden="true"><use href="#ci-star"/></svg>
            {{__("Good ratings only")}}
        </button>
        <button type="button" class="cat-chip" data-filter="low">{{__("Hide « few votes »")}}</button>
    </div>

    <div class="cat-legend" data-rated-tools>
        <span><span class="cat-badge tier-a" title="{{__('Weighted rating of :score or more', ['score' => number_format(\App\Models\Url::SCORE_EXCELLENT, 1, ',', '')])}}">8,1</span>{{__("Excellent")}}</span>
        <span><span class="cat-badge tier-b" title="{{__('Weighted rating of :score or more', ['score' => number_format(\App\Models\Url::SCORE_GOOD, 1, ',', '')])}}">7,0</span>{{__("Good")}}</span>
        <span><span class="cat-badge tier-c" title="{{__('Weighted rating below :score', ['score' => number_format(\App\Models\Url::SCORE_GOOD, 1, ',', '')])}}">5,9</span>{{__("Average")}}</span>
        <span><span class="cat-badge tier-low" title="{{__('Fewer than :votes votes on TMDB: the TMDB rating is shown, but it is counted closer to the average for sorting', ['votes' => \App\Models\Url::LOW_VOTES])}}">9,0?</span>{{__("Few votes, rating not reliable")}}</span>
        <span><span class="cat-badge tier-none" title="{{__('No TMDB rating for now.')}}">—</span>{{__("Not rated yet")}}</span>
        <span class="cat-legend-note">{{__("The rating takes the number of votes into account. Use the arrow keys to browse.")}}</span>
    </div>

    @if (count($urls["movies"]) > 0)
        <div class="cat-pane" data-pane="movies" role="tabpanel" hidden>
            <p class="cat-count"></p>
            <div class="cat-layout">
                <div class="cat-grid">
                    @foreach ($urls["movies"] as $url)
                        @php($parsed = $url->parsedName())
                        @php($rating = $url->rating())
                        <button type="button" class="cat-card" data-order="{{$loop->index}}" data-id="{{$url->id}}" data-score="{{$url->score}}"
                                data-year="{{$parsed['year'] ?? $url->year}}" data-title="{{mb_strtolower($parsed['title'])}}"
                                data-good="{{$rating['good'] ? 1 : 0}}" data-low="{{$rating['low'] ? 1 : 0}}"
                                aria-label="{{$parsed['title']}}, {{$rating['label']}} {{$rating['badge']}}">
                            <span class="cat-poster">
                                <img loading="lazy" src="{{($url->picture != '') ? "/picture/".$url->id : '/images/default.webp'}}" alt="">
                                <span class="cat-badge tier-{{$rating['tier']}}" title="{{$rating['tooltip']}}">{{$rating['badge']}}</span>
                                @if ($url->isDoubtful())
                                    <span class="cat-doubt" title="{{__("TMDB data to check")}}">!</span>
                                @endif
                                <span class="cat-flags">
                                    <span class="cat-flag-fav" @if (!$url->isFavorite(Auth::id())) hidden @endif>
                                        <svg width="14" height="14" aria-hidden="true"><use href="#ci-heart-fill"/></svg>
                                    </span>
                                    <span class="cat-flag-seen" @if (!$url->isWatched(Auth::id())) hidden @endif>{{__("Seen")}}</span>
                                </span>
                            </span>
                            <span class="cat-title">{{$parsed['title']}}</span>
                            <span class="cat-meta">{{implode(' · ', array_filter(array_merge([$parsed['year'] ?? ($url->year ?: null)], $parsed['tags'])))}}</span>
                            <span class="cat-sub">{{$rating['sub']}}</span>
                        </button>
                    @endforeach
                </div>
                <aside class="cat-detail" aria-label="{{__("Title details")}}">
                    @foreach ($urls["movies"] as $url)
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
                        </div>
                    @endforeach
                </aside>
            </div>
        </div>
    @endif

    @if (count($urls["series"]) > 0)
        <div class="cat-pane" data-pane="series" role="tabpanel" hidden>
            <p class="cat-count"></p>
            <div class="cat-layout">
                <div class="cat-grid">
                    @foreach ($urls["series"] as $serie => $seasons)
                        @php($first = $firstEpisodes[$serie])
                        @php($parsed = $first->parsedName())
                        @php($rating = $first->rating())
                        @php($nbEpisodes = array_sum(array_map('count', $seasons)))
                        <button type="button" class="cat-card" data-order="{{$loop->index}}" data-id="s{{$first->id}}" data-score="{{$first->score}}"
                                data-year="{{$first->year}}" data-title="{{mb_strtolower($parsed['title'])}}"
                                data-good="{{$rating['good'] ? 1 : 0}}" data-low="{{$rating['low'] ? 1 : 0}}"
                                aria-label="{{$parsed['title']}}, {{$rating['label']}} {{$rating['badge']}}">
                            <span class="cat-poster">
                                <img loading="lazy" src="{{($first->picture != '') ? "/picture/".$first->id : '/images/default.webp'}}" alt="">
                                <span class="cat-badge tier-{{$rating['tier']}}" title="{{$rating['tooltip']}}">{{$rating['badge']}}</span>
                                <span class="cat-flags">
                                    <span class="cat-flag-fav" @if (!$first->isFavorite(Auth::id())) hidden @endif>
                                        <svg width="14" height="14" aria-hidden="true"><use href="#ci-heart-fill"/></svg>
                                    </span>
                                </span>
                            </span>
                            <span class="cat-title">{{$parsed['title']}}</span>
                            <span class="cat-meta">{{implode(' · ', array_filter(array_merge([$first->year ?: null, trans_choice(':count season|:count seasons', count($seasons)), trans_choice(':count ep.|:count ep.', $nbEpisodes)], $parsed['tags'])))}}</span>
                            <span class="cat-sub">{{$rating['sub']}}</span>
                        </button>
                    @endforeach
                </div>
                <aside class="cat-detail" aria-label="{{__("Series details")}}">
                    @foreach ($urls["series"] as $serie => $seasons)
                        @php($first = $firstEpisodes[$serie])
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
                    @endforeach
                </aside>
            </div>
        </div>
    @endif

    @if (count($urls["channels"]) > 0)
        <div class="cat-pane" data-pane="channels" role="tabpanel" hidden>
            <div class="cat-grid cat-grid-channels">
                @foreach ($urls["channels"] as $url)
                    @php($channel = $url->channelName())
                    <div class="cat-channel">
                        <a href="iptv://{{$url->urlFinal}}" data-id="{{$url->id}}" class="cat-channel-link" title="{{$url->name}}">
                            @if ($url->picture != '')
                                <img class="cat-logo" loading="lazy" src="/picture/{{$url->id}}" alt="" data-name="{{$channel['name']}}" onerror="catalogLogoMissing(this)">
                            @else
                                <span class="cat-logo cat-logo-text" aria-hidden="true">{{$channel['name']}}</span>
                            @endif
                            <span class="cat-channel-name">{{$channel['name']}}</span>
                        </a>
                        @if ($channel['tags'])
                            <div class="cat-tags cat-channel-tags">
                                @foreach ($channel['tags'] as $tag)
                                    <span>{{$tag}}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="cat-channel-actions">
                            <button type="button" class="cat-icon @if ($url->isFavorite(Auth::id())) active @endif" onclick="addFavorite(this, {{$url->id}})" aria-label="{{__("Favorite")}}">
                                <svg width="16" height="16" aria-hidden="true"><use href="#ci-heart"/></svg>
                            </button>
                            <button type="button" class="cat-icon" onclick="copyLink(@js($url->urlFinal))" aria-label="{{__("Copy link")}}">
                                <svg width="16" height="16" aria-hidden="true"><use href="#ci-copy"/></svg>
                            </button>
                            <button type="button" class="cat-icon" title="{{$tvLink}}" onclick="addTvStream(this, @js($url->url), @js($tvLink))" aria-label="{{__("Send to TV")}}">
                                <svg width="16" height="16" aria-hidden="true"><use href="#ci-tv"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    $(".stream").on("click", function() {
        addCounter($(this).attr("data-id"));
    });

    function copyLink(url){
        navigator.clipboard.writeText(url);
    }

    Catalog.init(document.getElementById("catalog"));
</script>
