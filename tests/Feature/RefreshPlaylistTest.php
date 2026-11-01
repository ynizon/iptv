<?php

namespace Tests\Feature;

use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefreshPlaylistTest extends TestCase
{
    use RefreshDatabase;

    protected string $file;

    protected function setUp(): void
    {
        parent::setUp();

        // The playlist is read from a local file, through curl as a real one
        $this->file = tempnam(sys_get_temp_dir(), 'm3u');
        DB::table('playlists')->insert(['name' => 'Test', 'url' => 'file:///' . ltrim(str_replace('\\', '/', $this->file), '/'),
            'created_at' => now(), 'updated_at' => now()]);
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    /**
     * Write the playlist then refresh: [name, group, url] per title
     */
    protected function refresh(array $titles): void
    {
        $content = "#EXTM3U\n";
        foreach ($titles as [$name, $group, $url]) {
            $content .= '#EXTINF:-1 tvg-id="" tvg-name="' . $name . '" tvg-logo="" group-title="' . $group . '",' . $name . "\n" . $url . "\n";
        }
        file_put_contents($this->file, $content);

        $this->artisan('refresh:playlist')->assertSuccessful();
    }

    protected function movie(string $name, string $group, string $token, int $stream): array
    {
        return [$name, $group, "http://provider:80/movie/user/$token/$stream.mkv"];
    }

    public function test_the_first_import_creates_the_urls_without_a_date(): void
    {
        $this->refresh([
            $this->movie('Alpha (FR) FHD 2024', 'COMEDIE', 'token', 1),
            $this->movie('Beta (FR) FHD 2023', 'COMEDIE', 'token', 2),
        ]);

        $this->assertSame(2, Url::count());
        // Otherwise the whole catalog would be shown as new
        $this->assertSame(0, Url::whereNotNull('created_at')->count());
    }

    public function test_the_refresh_follows_moved_changed_removed_and_new_titles(): void
    {
        $unchanged = [
            $this->movie('Echo (FR) FHD 2020', 'COMEDIE', 'token', 5),
            $this->movie('Foxtrot (FR) FHD 2020', 'COMEDIE', 'token', 6),
            $this->movie('Golf (FR) FHD 2020', 'COMEDIE', 'token', 7),
        ];
        $this->refresh(array_merge([
            $this->movie('Alpha (FR) FHD 2024', 'COMEDIE', 'old-token', 1),
            $this->movie('Beta (FR) FHD 2023', 'FILMS RÉCEMMENT AJOUTÉS', 'token', 2),
            $this->movie('Charlie (FR) FHD 2022', 'COMEDIE', 'token', 3),
        ], $unchanged));

        $alpha = Url::where('name', 'Alpha (FR) FHD 2024')->first();
        DB::table('urls')->where('id', $alpha->id)->update(['imdb' => 'Summary from TMDB']);
        $user = User::factory()->create(['formats' => '[]']);
        DB::table('views')->insert(['user_id' => $user->id, 'url' => $alpha->url, 'favorite' => 1, 'counter' => 42]);

        $this->refresh(array_merge([
            // New token in the url, same stream
            $this->movie('Alpha (FR) FHD 2024', 'COMEDIE', 'new-token', 1),
            // Moved out of the "recently added" category
            $this->movie('Beta (FR) FHD 2023', 'COMEDIE', 'token', 2),
            // Charlie is gone, Delta is new
            $this->movie('Delta (FR) FHD 2025', 'COMEDIE', 'token', 4),
        ], $unchanged));

        // Same row, with its TMDB data, its favorite and its progress
        $followed = Url::find($alpha->id);
        $this->assertStringContainsString('/new-token/', $followed->url);
        $this->assertSame('Summary from TMDB', $followed->imdb);
        $this->assertDatabaseHas('views', ['user_id' => $user->id, 'url' => $followed->url, 'favorite' => 1, 'counter' => 42]);

        $this->assertSame('COMEDIE', Url::where('name', 'Beta (FR) FHD 2023')->value('category'));
        $this->assertFalse(Url::where('name', 'Charlie (FR) FHD 2022')->exists());
        $this->assertNotNull(Url::where('name', 'Delta (FR) FHD 2025')->value('created_at'));
        // Charlie removed, Delta added
        $this->assertSame(6, Url::count());
    }

    public function test_nothing_is_removed_when_most_of_the_playlist_is_missing(): void
    {
        $this->refresh([
            $this->movie('Alpha (FR) FHD 2024', 'COMEDIE', 'token', 1),
            $this->movie('Beta (FR) FHD 2023', 'COMEDIE', 'token', 2),
            $this->movie('Charlie (FR) FHD 2022', 'COMEDIE', 'token', 3),
            $this->movie('Delta (FR) FHD 2021', 'COMEDIE', 'token', 4),
        ]);

        // A cut download
        $this->refresh([
            $this->movie('Alpha (FR) FHD 2024', 'COMEDIE', 'token', 1),
        ]);

        $this->assertSame(4, Url::count());
    }
}
