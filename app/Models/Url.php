<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Url extends Model
{
    use HasFactory;

    /**
     * Recompute the "filter" flag of every url from scratch:
     * hidden when its category is not in the whitelist (if any)
     * or matches a filter, or when it is a playlist separator
     */
    public static function applyFilters(): void
    {
        // Only a few hundred categories: decide in PHP, then update every url in one query
        $visibles = VisibleCategory::pluck("name")->flip();
        $filters = Filter::pluck("name");
        $hidden = DB::table('urls')->distinct()->pluck('category')
            ->filter(fn ($category) => ($visibles->isNotEmpty() && !$visibles->has($category))
                || $filters->contains(fn ($filter) => mb_stripos($category, $filter) !== false))
            ->values()->all();

        $placeholders = implode(",", array_fill(0, count($hidden), "?")) ?: "NULL";
        // Separators like "------▼|FR|-SPORTS-|FR|▼------" or "▀▄ VOD ▀▄▀▄ 3D ▄▀▄▀"
        $hiddenSql = "CASE WHEN category IN ($placeholders) OR name LIKE '------%' OR name LIKE '▀▄%' THEN 1 ELSE 0 END";
        // Only rewrite the rows that change
        DB::update("UPDATE urls SET filter = $hiddenSql WHERE filter <> $hiddenSql", array_merge($hidden, $hidden));

        Category::clearCache();
    }

    /**
     * The provider hosts of the series posters are dead, but their files
     * are TMDB posters with the same name: point to TMDB instead
     */
    public static function normalizePicture(string $picture): string
    {
        if (preg_match('#^https?://[^/]+/images/series/([^/?]+\.(?:jpe?g|png|webp))$#i', $picture, $matches)) {
            return "https://image.tmdb.org/t/p/w300_and_h450_bestv2/" . $matches[1];
        }
        // No file name at all
        if (preg_match('#^https?://[^/]+/images/series/?$#i', $picture)) {
            return "";
        }

        return $picture;
    }

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    /**
     * Views of each user, keyed by url, loaded once per request
     * instead of one query per url and per method
     */
    protected static array $userViews = [];

    protected function userView($user_id): ?View
    {
        if (!isset(self::$userViews[$user_id])) {
            self::$userViews[$user_id] = View::where("user_id", $user_id)->get()->keyBy("url");
        }
        return self::$userViews[$user_id]->get($this->url);
    }

    public function isFavorite($user_id) {
        $view = $this->userView($user_id);
        return $view ? $view->favorite : 0;
    }

    public function isWatched($user_id) {
        $view = $this->userView($user_id);
        return $view ? $view->watched : 0;
    }

    public function counter($user_id) {
        $view = $this->userView($user_id);
        return $view ? self::formatHour($view->counter) : self::formatHour(0);
    }

    public function counterMin($user_id) {
        $view = $this->userView($user_id);
        return $view ? $view->counter : 0;
    }

    public function counterSec($user_id) {
        return $this->counterMin($user_id) * 60;
    }

    public static function formatHour(int $minutesTime) : string {
        if ($minutesTime == 0) {return '';}
        $hours = intdiv($minutesTime, 60);
        $minutes = $minutesTime % 60;
        if ($minutes <10){
            $minutes = "0".$minutes;
        }

        return $hours . ":" . $minutes;
    }
}
