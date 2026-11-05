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
<div class="catalog" id="catalog" data-recent="{{ request('category') == -2 ? 1 : 0 }}" data-added="{{ \App\Models\Category::isRecentlyAdded(request('category')) ? 1 : 0 }}" data-labels="{{ json_encode(['one' => __(':count title'), 'many' => __(':count titles'), 'bestFirst' => __(', best rated first'), 'newestFirst' => __(', most recent first'), 'addedFirst' => __(', last added first'), 'loadError' => __('The details could not be loaded. Select the title again to retry.'),'empty' => __('No title matches. Try without « Good ratings only ».')]) }}">
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
        @if (\App\Models\Category::isRecentlyAdded(request('category')))
            <button type="button" class="cat-chip" data-sort="added">{{__("Recently added")}}</button>
        @endif
        <button type="button" class="cat-chip" data-sort="year">{{__("Most recent")}}</button>
        <button type="button" class="cat-chip" data-sort="score">{{__("Best rated")}}</button>
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
                        {{-- The other versions of the same movie (resolution, language...) are in its detail --}}
                        @php($others = $versions[$url->id] ?? [])
                        @php($tags = collect([$url, ...$others])->flatMap(fn ($version) => $version->parsedName()['tags'])->unique()->all())
                        <button type="button" class="cat-card" data-order="{{$loop->index}}" data-id="{{$url->id}}" data-score="{{$url->score}}"
                                @if ($others) data-versions="{{collect($others)->pluck('id')->implode(',')}}" @endif
                                data-year="{{$parsed['year'] ?? $url->year}}" data-title="{{mb_strtolower($parsed['title'])}}"
                                data-good="{{$rating['good'] ? 1 : 0}}" data-low="{{$rating['low'] ? 1 : 0}}"
                                aria-label="{{$parsed['title']}}, {{$rating['label']}} {{$rating['badge']}}">
                            <span class="cat-poster">
                                <img loading="lazy" src="{{$url->pictureUrl()}}" alt="">
                                <span class="cat-badge tier-{{$rating['tier']}}" title="{{$rating['tooltip']}}">{{$rating['badge']}}</span>
                                @if ($url->isDoubtful())
                                    <span class="cat-doubt" title="{{__("TMDB data to check")}}">!</span>
                                @endif
                                @if (collect([$url, ...$others])->contains(fn ($version) => $version->isNew()))
                                    <span class="cat-new">{{__("New")}}</span>
                                @endif
                                <span class="cat-flags">
                                    <span class="cat-flag-fav" @if (!$url->isFavorite(Auth::id())) hidden @endif>
                                        <svg width="14" height="14" aria-hidden="true"><use href="#ci-heart-fill"/></svg>
                                    </span>
                                    <span class="cat-flag-seen" @if (!$url->isWatched(Auth::id())) hidden @endif>{{__("Seen")}}</span>
                                </span>
                            </span>
                            <span class="cat-title">{{$parsed['title']}}</span>
                            <span class="cat-meta">{{implode(' · ', array_filter(array_merge([$parsed['year'] ?? ($url->year ?: null)], $tags, [$others ? trans_choice(':count version|:count versions', count($others) + 1) : null])))}}</span>
                            <span class="cat-sub">{{$rating['sub']}}</span>
                        </button>
                    @endforeach
                </div>
                <aside class="cat-detail" aria-label="{{__("Title details")}}">
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
                        {{-- The whole series, not only the episodes of this page --}}
                        @php($nbSeasons = max(count($seasons), $seriesTotals[$serie]["seasons"] ?? 0))
                        @php($nbEpisodes = max(array_sum(array_map('count', $seasons)), $seriesTotals[$serie]["episodes"] ?? 0))
                        <button type="button" class="cat-card" data-order="{{$loop->index}}" data-id="s{{$first->id}}" data-score="{{$first->score}}"
                                data-year="{{$first->year}}" data-title="{{mb_strtolower($parsed['title'])}}"
                                data-good="{{$rating['good'] ? 1 : 0}}" data-low="{{$rating['low'] ? 1 : 0}}"
                                aria-label="{{$parsed['title']}}, {{$rating['label']}} {{$rating['badge']}}">
                            <span class="cat-poster">
                                <img loading="lazy" src="{{$first->pictureUrl()}}" alt="">
                                <span class="cat-badge tier-{{$rating['tier']}}" title="{{$rating['tooltip']}}">{{$rating['badge']}}</span>
                                @if (collect($seasons)->flatten()->contains(fn ($episode) => $episode->isNew()))
                                    <span class="cat-new">{{__("New")}}</span>
                                @endif
                                <span class="cat-flags">
                                    <span class="cat-flag-fav" @if (!$first->isFavorite(Auth::id())) hidden @endif>
                                        <svg width="14" height="14" aria-hidden="true"><use href="#ci-heart-fill"/></svg>
                                    </span>
                                </span>
                            </span>
                            <span class="cat-title">{{$parsed['title']}}</span>
                            <span class="cat-meta">{{implode(' · ', array_filter(array_merge([$first->year ?: null, trans_choice(':count season|:count seasons', $nbSeasons), trans_choice(':count ep.|:count ep.', $nbEpisodes)], $parsed['tags'])))}}</span>
                            <span class="cat-sub">{{$rating['sub']}}</span>
                        </button>
                    @endforeach
                </div>
                <aside class="cat-detail" aria-label="{{__("Series details")}}">
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
                                <img class="cat-logo" loading="lazy" src="{{$url->pictureUrl()}}" alt="" data-name="{{$channel['name']}}" onerror="catalogLogoMissing(this)">
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
                            <button type="button" class="cat-icon" title="{{$tvLink}}" onclick="addTvStream(this, @js($url->urlFinal), @js($tvLink))" aria-label="{{__("Send to TV")}}">
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
    // On the catalog: the detail panels are loaded later
    $("#catalog").on("click", ".stream", function() {
        addCounter($(this).attr("data-id"));
    });

    function copyLink(url){
        navigator.clipboard.writeText(url);
    }

    Catalog.init(document.getElementById("catalog"));
</script>
