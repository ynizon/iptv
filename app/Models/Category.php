<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    // A category page stays cached for a week, or until the next refresh
    public const PAGE_TTL = 7 * 24 * 3600;

    // Virtual category of the main French channels (-1 and -2 are the favorites and the recent ones)
    public const MAIN_TV = "-3";

    // The main French channels in the TNT order: the names given by the provider, without the quality
    public const MAIN_CHANNELS = [
        ['TF1'], ['FRANCE 2'], ['FRANCE 3'], ['CANAL+ (DOUBLE AUDIO)', 'CANAL+'], ['FRANCE 5'], ['M6'], ['ARTE'],
        ['C8'], ['W9'], ['TMC'], ['TFX'], ['NRJ 12', 'NRJ12'], ['LCP'], ['FRANCE 4'], ['BFM TV'], ['CNEWS'],
        ['CSTAR'], ['GULLI'], ['TF1 SERIES FILMS'], ["L'EQUIPE", "L'ÉQUIPE"], ['6TER'], ['RMC STORY'],
        ['RMC DECOUVERTE', 'RMC DÉCOUVERTE'], ['CHERIE 25', 'CHÉRIE 25'], ['LCI'], ['FRANCE INFO', 'FRANCEINFO'],
    ];

    protected $fillable = [
        'from',
        'to'
    ];

    /**
     * Group the visible playlist categories by display name
     * @return array display name => list of original categories
     */
    public static function groups(): array
    {
        return Cache::rememberForever("category_groups", fn () => self::computeGroups());
    }

    /**
     * Forget the cached groups and pages, to call when urls, filters or renames change
     */
    public static function clearCache(): void
    {
        Cache::forget("category_groups");
        Cache::store("catalog")->flush();
    }

    /**
     * Urls of a category page ("" for every category), cached for a week
     */
    public static function pageUrls(string $category): Collection
    {
        // Raw attributes, compressed: a page holds up to 6000 urls
        $rows = Cache::store("catalog")->remember(self::pageKey($category), self::PAGE_TTL,
            fn () => gzcompress(serialize(self::queryPageUrls($category)->map->getAttributes()->all())));

        return Url::hydrate(unserialize(gzuncompress($rows)));
    }

    /**
     * Rebuild the cache of every category page, to call after a refresh
     * @return int number of pages
     */
    public static function warmPages(): int
    {
        self::clearCache();
        $categories = array_merge(["", self::MAIN_TV], array_keys(self::groups()));
        foreach ($categories as $category) {
            self::pageUrls($category);
        }

        return count($categories);
    }

    protected static function pageKey(string $category): string
    {
        return "page_" . md5($category);
    }

    /**
     * One url per main French channel, in the TNT order: the full HD one, else HD, else SD
     * (the 4K, HEVC and backup streams are left out, less reliable)
     */
    public static function mainChannels(): Collection
    {
        $channels = [];
        $positions = [];
        foreach (self::MAIN_CHANNELS as $position => $names) {
            foreach ($names as $alias => $name) {
                $positions[mb_strtoupper($name)] = [$position, $alias];
            }
        }
        $quality = fn (array $tags) => match ($tags) {
            ['FHD'] => 0, ['HD'] => 1, [] => 2, ['SD'] => 3, default => null,
        };

        $candidates = Url::where("tvchannel", 1)->where("filter", 0)->where("name", "like", "|FR|%")->orderBy("id")->get();
        foreach ($candidates as $url) {
            $channel = $url->channelName();
            [$position, $alias] = $positions[mb_strtoupper($channel['name'])] ?? [null, null];
            $rank = $quality($channel['tags']);
            if ($position === null || $rank === null) {
                continue;
            }
            // The first name of the channel first, then the best quality
            $score = $alias * 10 + $rank;
            if (!isset($channels[$position]) || $score < $channels[$position][0]) {
                $channels[$position] = [$score, $url];
            }
        }
        ksort($channels);

        return new Collection(array_column($channels, 1));
    }

    protected static function queryPageUrls(string $category): Collection
    {
        if ($category === self::MAIN_TV) {
            return self::mainChannels();
        }
        $query = Url::where("filter", "=", 0);
        if ($category != '') {
            $query->whereIn("category", self::groups()[$category] ?? [$category]);
        }

        return Url::limitByType($query, self::isRecentlyAdded($category));
    }

    /**
     * "FILMS RÉCEMMENT AJOUTÉS", "RECEMMENT AJOUTÉES": shown last added first
     */
    public static function isRecentlyAdded(?string $category): bool
    {
        return str_contains(Str::upper(Str::ascii((string) $category)), "RECEMMENT AJOUT");
    }

    protected static function computeGroups(): array
    {
        // Longest rules first, so "BRAQUAGE & ARNAQUE" is renamed before "ARNAQUE"
        $renames = self::all()->sortByDesc(fn ($rename) => mb_strlen($rename->from));
        $originals = DB::table('urls')->where("filter", "=", 0)
            ->distinct()->orderBy("category")->pluck('category');

        $groups = [];
        foreach ($originals as $original) {
            $name = self::displayName($original, $renames);
            if ($name != '') {
                $groups[$name][] = $original;
            }
        }
        ksort($groups);

        return $groups;
    }

    /**
     * Apply the renames, then unify the separators ("|", "&", " - ") so that
     * "MAFIA & GANG" and "MAFIA | GANG" end up in the same group
     */
    protected static function displayName(string $category, $renames): string
    {
        $category = trim($category);
        foreach ($renames as $rename) {
            $category = str_replace($rename->from, (string) $rename->to, $category);
        }
        $category = preg_replace('/\s*[|&]\s*|\s+-\s+/u', ' - ', $category);
        $category = preg_replace('/\s+/u', ' ', $category);

        return mb_strtoupper(trim($category, " -"));
    }
}
