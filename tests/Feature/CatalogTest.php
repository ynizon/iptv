<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Url;
use App\Models\User;
use App\View\Components\Categories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected int $playlist;
    protected int $stream = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->playlist = DB::table('playlists')->insertGetId(['name' => 'Test', 'url' => 'http://provider/get.php']);
    }

    protected function url(string $name, string $category, array $attributes = []): Url
    {
        $this->stream++;
        $serie = preg_match('/S(\d{2}) E(\d{2})/', $name, $matches) ? 1 : 0;
        $id = DB::table('urls')->insertGetId(array_merge([
            'url' => "http://provider/movie/user/token/{$this->stream}.mkv",
            'name' => $name,
            'short_name' => $serie ? trim(preg_replace('/S\d{2} E\d{2}/', '', $name)) : $name,
            'picture' => '',
            'category' => $category,
            'movie' => $serie ? 0 : 1,
            'serie' => $serie,
            'season' => $serie ? (int) $matches[1] : 0,
            'episod' => $serie ? (int) $matches[2] : 0,
            'playlist_id' => $this->playlist,
        ], $attributes));
        Category::clearCache();

        return Url::find($id);
    }

    protected function user(): User
    {
        // Every format kept: none hidden
        return User::factory()->create(['formats' => json_encode(\App\Http\Controllers\SearchController::FILTERS)]);
    }

    public function test_the_recently_added_categories_come_first_in_the_menu(): void
    {
        $this->url('Alpha (FR) FHD 2024', 'ACTION');
        $this->url('Beta (FR) FHD 2024', 'FILMS RÉCEMMENT AJOUTÉS');
        $this->url('Charlie (FR) FHD 2024', 'AVENTURE');

        $html = (new Categories())->render()->render();
        preg_match_all('/class="category-name">([^<]*)</', $html, $matches);

        $this->assertSame(['FILMS RÉCEMMENT AJOUTÉS', 'ACTION', 'AVENTURE'], $matches[1]);
    }

    public function test_a_category_shows_the_most_recent_movies_first(): void
    {
        $this->url('Old (FR) FHD 1995', 'ACTION', ['score' => 9]);
        $this->url('New (FR) FHD 2024', 'ACTION', ['score' => 5]);
        $this->url('Middle (FR) FHD 2010', 'ACTION');

        $this->assertSame(['New (FR) FHD 2024', 'Middle (FR) FHD 2010', 'Old (FR) FHD 1995'],
            Category::pageUrls('ACTION')->pluck('name')->all());
    }

    public function test_a_recently_added_category_shows_the_last_added_first(): void
    {
        // The first import (no date) by release year, after the titles dated by a refresh
        $this->url('First import (FR) FHD 2024', 'FILMS RÉCEMMENT AJOUTÉS');
        $this->url('Old classic (FR) FHD 1979', 'FILMS RÉCEMMENT AJOUTÉS');
        $this->url('Added yesterday (FR) FHD 2020', 'FILMS RÉCEMMENT AJOUTÉS', ['created_at' => now()->subDay()]);
        $this->url('Added today (FR) FHD 2015', 'FILMS RÉCEMMENT AJOUTÉS', ['created_at' => now()]);

        $this->assertSame(['Added today (FR) FHD 2015', 'Added yesterday (FR) FHD 2020', 'First import (FR) FHD 2024', 'Old classic (FR) FHD 1979'],
            Category::pageUrls('FILMS RÉCEMMENT AJOUTÉS')->pluck('name')->all());
    }

    public function test_the_search_ignores_accents_and_matches_the_start_of_words(): void
    {
        $this->url('Amélie Poulain (FR) FHD 2001', 'COMEDIE');
        $this->url('Harry Potter (MULTI) FHD 2001', 'AVENTURE');

        $this->assertSame(['Amélie Poulain (FR) FHD 2001'], Url::searchName('amelie')->pluck('name')->all());
        $this->assertSame(['Harry Potter (MULTI) FHD 2001'], Url::searchName('harr pot')->pluck('name')->all());
        $this->assertSame(0, Url::searchName('otter')->count());
    }

    public function test_the_category_page_cache_is_cleared_with_the_categories(): void
    {
        $this->url('Alpha (FR) FHD 2024', 'ACTION');
        $this->assertCount(1, Category::pageUrls('ACTION'));

        DB::table('urls')->insert(['url' => 'http://provider/movie/user/token/99.mkv', 'name' => 'Beta (FR) FHD 2024', 'picture' => '',
            'category' => 'ACTION', 'movie' => 1, 'playlist_id' => $this->playlist]);
        // Still the cached page
        $this->assertCount(1, Category::pageUrls('ACTION'));

        Category::clearCache();
        $this->assertCount(2, Category::pageUrls('ACTION'));
    }

    public function test_the_catalog_page_loads_the_details_on_demand(): void
    {
        $movie = $this->url('Alpha (FR) FHD 2024', 'ACTION', ['created_at' => now(), 'duration' => 113]);

        $page = $this->actingAs($this->user())->post('/search', ['search' => '', 'category' => 'ACTION']);
        $page->assertOk()->assertSee('Alpha')->assertSee('cat-new', false)->assertDontSee('data-detail="' . $movie->id . '"', false);

        // The length stops the progress counter at the end of the movie
        $this->get('/detail/' . $movie->id)->assertOk()->assertSee('data-detail="' . $movie->id . '"', false)
            ->assertSee('data-duration="113"', false);
    }

    public function test_a_series_card_counts_all_its_episodes(): void
    {
        // The provider spreads the series over two categories
        $first = $this->url('Lupin (MULTI) FHD S01 E01', 'SERIES');
        $this->url('Lupin (MULTI) FHD S01 E02', 'SERIES');
        $this->url('Lupin (MULTI) FHD S02 E01', 'RECEMMENT AJOUTÉES');
        $user = $this->user();

        $this->actingAs($user)->post('/search', ['search' => '', 'category' => 'SERIES'])
            ->assertOk()->assertSee('2 saisons')->assertSee('3 ép.');

        $this->get('/detail_serie/' . $first->id)->assertOk()->assertSee('data-detail="s' . $first->id . '"', false)
            ->assertSee('Saison 02');
    }

    public function test_the_tv_category_gives_one_stream_per_main_channel_in_the_tnt_order(): void
    {
        $channel = ['tvchannel' => 1, 'movie' => 0];
        $this->url('|FR| FRANCE 2 HD', 'FR TV HD (France)', $channel);
        $this->url('|FR| TF1 HD', 'FR TV HD (France)', $channel);
        $this->url('|FR| TF1 FHD', 'FR TV FULL HD|4K  (France)', $channel);
        $this->url('|FR| TF1 4K HDR UHD (Résolution Exclus)', 'FR TV FULL HD|4K  (France)', $channel);
        $this->url('|FR| M6 HEVC', 'FR TV HEVC (France)', $channel);
        $this->url('|FR| PARIS PREMIERE FHD', 'FR TV FULL HD|4K  (France)', $channel);

        $this->assertSame(['|FR| TF1 FHD', '|FR| FRANCE 2 HD'], Category::pageUrls(Category::MAIN_TV)->pluck('name')->all());

        $this->actingAs($this->user())->post('/search', ['search' => 'france', 'category' => Category::MAIN_TV])
            ->assertOk()->assertSee('FRANCE 2')->assertDontSee('TF1');
    }

    public function test_the_versions_of_a_movie_are_grouped_on_one_card(): void
    {
        $hd = $this->url('La Fracture (FR) HD 2021', 'DRAME');
        $fhd = $this->url('La Fracture (FR) FHD 2021', 'DRAME');
        $uhd = $this->url('La Fracture (4K) HDR 2021', 'DRAME');
        // Same title, another movie
        $this->url('La Fracture (FR) FHD 1990', 'DRAME');

        $page = $this->actingAs($this->user())->post('/search', ['search' => '', 'category' => 'DRAME'])->assertOk();
        // The 1080p one shows, with the others
        $page->assertSee('data-id="' . $fhd->id . '"', false)->assertDontSee('data-id="' . $hd->id . '"', false)
            ->assertSee('data-versions="' . $uhd->id . ',' . $hd->id . '"', false)->assertSee('3 versions');
        $this->assertSame(2, substr_count($page->getContent(), 'class="cat-card"'));

        $this->get('/detail/' . $fhd->id . '?versions=' . $uhd->id . ',' . $hd->id)->assertOk()
            ->assertSee('Autres versions')->assertSee('ahref-' . $hd->id, false)->assertSee('4K · HDR');
    }

    public function test_the_news_are_the_last_added_movies_and_series(): void
    {
        $this->url('Added today (FR) FHD 2015', 'ACTION', ['created_at' => now()]);
        $this->url('In the provider news (FR) FHD 2024', 'FILMS RÉCEMMENT AJOUTÉS');
        $this->url('Lupin (MULTI) FHD S01 E01', 'SERIES', ['created_at' => now()->subDays(3)]);
        // Too old, or a channel
        $this->url('Added two months ago (FR) FHD 2025', 'ACTION', ['created_at' => now()->subDays(60)]);
        $this->url('|FR| TF1 FHD', 'FR TV FULL HD|4K  (France)', ['tvchannel' => 1, 'movie' => 0, 'created_at' => now()]);

        $this->assertSame(['Added today (FR) FHD 2015', 'In the provider news (FR) FHD 2024', 'Lupin (MULTI) FHD S01 E01'],
            Category::pageUrls(Category::NEWS)->pluck('name')->all());

        // Sorted last added first in the page too
        $this->actingAs($this->user())->post('/search', ['search' => '', 'category' => Category::NEWS])
            ->assertOk()->assertSee('data-added="1"', false)->assertSee('data-sort="added"', false);
    }

    public function test_play_gives_a_vlc_playlist_starting_at_the_resume_position(): void
    {
        $movie = $this->url('Fast & Furious (MULTI) FHD 2009', 'ACTION');

        $user = $this->user();
        $response = $this->actingAs($user)->get('/play/' . $movie->id . '?start=754');

        $response->assertOk()->assertHeader('Content-Type', 'application/xspf+xml')
            ->assertHeader('Content-Disposition', 'attachment; filename="fast-furious-multi-fhd-2009.xspf"');
        $xml = simplexml_load_string($response->getContent());
        // The stream link of the site, not the provider url
        $this->assertSame(url('/s/' . $user->fresh()->tv_token . '/' . $movie->id . '.mkv'), (string) $xml->trackList->track->location);
        $this->assertSame('Fast & Furious (MULTI) FHD 2009', (string) $xml->trackList->track->title);
        $this->assertStringContainsString('<vlc:option>start-time=754</vlc:option>', $response->getContent());

        // From the start: no option
        $this->assertStringNotContainsString('start-time', $this->get('/play/' . $movie->id)->getContent());
    }

    public function test_the_pages_give_stream_links_of_the_site_not_the_provider_urls(): void
    {
        $movie = $this->url('Alpha (FR) FHD 2024', 'ACTION');
        $channel = $this->url('|FR| TF1 FHD', 'FR TV FULL HD|4K  (France)', ['tvchannel' => 1, 'movie' => 0]);
        $user = $this->user();
        $this->actingAs($user);

        $detail = $this->get('/detail/' . $movie->id)->assertOk()->assertDontSee($movie->url, false);
        $detail->assertSee('iptv://' . url('/s/' . $user->fresh()->tv_token . '/' . $movie->id . '.mkv'), false);
        $this->post('/search', ['search' => '', 'category' => 'FR TV FULL HD|4K  (France)'])->assertOk()->assertDontSee($channel->url, false);
    }

    public function test_a_stream_link_redirects_to_the_provider_until_the_token_changes(): void
    {
        $movie = $this->url('Alpha (FR) FHD 2024', 'ACTION');
        $user = $this->user();
        $token = $user->tvToken();

        // No session: VLC or the TV
        $this->get('/s/' . $token . '/' . $movie->id . '.mkv')->assertRedirect($movie->url);
        $this->get('/s/wrong-token/' . $movie->id . '.mkv')->assertNotFound();

        // A link went out: new links
        $this->actingAs($user)->post('/users/' . $user->id . '/tv-token')->assertRedirect();
        $this->get('/s/' . $token . '/' . $movie->id . '.mkv')->assertNotFound();
        $this->get('/s/' . $user->fresh()->tv_token . '/' . $movie->id . '.mkv')->assertRedirect($movie->url);
    }

    public function test_only_an_admin_makes_new_links_for_another_user(): void
    {
        $other = $this->user();
        $token = $other->tvToken();

        $this->actingAs($this->user())->post('/users/' . $other->id . '/tv-token')->assertForbidden();
        $this->assertSame($token, $other->fresh()->tv_token);

        $this->actingAs(User::factory()->create(['admin' => true]))->post('/users/' . $other->id . '/tv-token')->assertRedirect();
        $this->assertNotSame($token, $other->fresh()->tv_token);
    }

    public function test_the_pictures_are_kept_by_the_browser_until_the_poster_changes(): void
    {
        $movie = $this->url('Alpha (FR) FHD 2024', 'ACTION', ['picture' => 'https://image.tmdb.org/t/p/w300/a.jpg']);
        $address = $movie->pictureUrl();
        DB::table('urls')->where('id', $movie->id)->update(['picture' => 'https://image.tmdb.org/t/p/w300/b.jpg']);
        $this->assertNotSame($address, Url::find($movie->id)->pictureUrl());

        // A picture already resized, in a public folder of the test
        $public = sys_get_temp_dir() . '/iptv-public-' . uniqid();
        mkdir("$public/images/playlist/{$movie->id}", 0777, true);
        copy(public_path('images/default.webp'), "$public/images/playlist/{$movie->id}/{$movie->id}.jpg");
        app()->usePublicPath($public);

        $this->actingAs($this->user())->get('/picture/' . $movie->id)->assertOk()
            ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');

        @unlink("$public/images/playlist/{$movie->id}/{$movie->id}.jpg");
    }
}
