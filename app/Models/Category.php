<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

    // A category page stays cached for a week, or until the next refresh
    public const PAGE_TTL = 7 * 24 * 3600;

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
        $categories = array_merge([""], array_keys(self::groups()));
        foreach ($categories as $category) {
            self::pageUrls($category);
        }

        return count($categories);
    }

    protected static function pageKey(string $category): string
    {
        return "page_" . md5($category);
    }

    protected static function queryPageUrls(string $category): Collection
    {
        $query = Url::where("filter", "=", 0);
        if ($category != '') {
            $query->whereIn("category", self::groups()[$category] ?? [$category]);
        }

        return Url::limitByType($query);
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
