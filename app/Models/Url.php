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

    // Weighted note: a note with few votes is pulled towards the average note
    public const SCORE_MEAN = 6.5;
    public const SCORE_WEIGHT = 200;
    // Below this number of votes, the note is shown as not reliable
    public const LOW_VOTES = 50;
    public const SCORE_EXCELLENT = 7.5;
    public const SCORE_GOOD = 6.5;

    protected ?array $parsedName = null;

    /**
     * Recompute the weighted note ("score") of every url, -1 when there is no note
     */
    public static function computeScores(): void
    {
        $scoreSql = "CASE WHEN note <= 0 THEN -1
            WHEN votes > 0 THEN (votes * note + " . self::SCORE_WEIGHT . " * " . self::SCORE_MEAN . ") / (votes + " . self::SCORE_WEIGHT . ")
            ELSE note END";
        DB::update("UPDATE urls SET score = $scoreSql WHERE score <> $scoreSql");
    }

    /**
     * "Harry Potter (MULTI) FHD 2001" -> title "Harry Potter", year 2001, tags ["VF + VO", "1080p"]
     */
    public function parsedName(): array
    {
        if ($this->parsedName !== null) {
            return $this->parsedName;
        }

        $labels = ['MULTI' => 'VF + VO', 'FR' => 'VF', 'VOST FR' => 'VOSTFR', 'VOSTFR' => 'VOSTFR',
            'BLU-RAY' => 'Blu-ray', 'HD' => '720p', 'FHD' => '1080p', 'UHD' => '4K', '4K' => '4K', '3D' => '3D'];
        $tags = [];
        $addTag = function ($tag) use (&$tags, $labels) {
            $label = $labels[strtoupper($tag)] ?? $tag;
            if (!in_array($label, $tags)) {
                $tags[] = $label;
            }
            return ' ';
        };

        $title = $this->short_name != '' ? $this->short_name : $this->name;
        $titleYear = null;
        if (preg_match('/\s(19\d{2}|20\d{2})\s*$/', $title, $matches, PREG_OFFSET_CAPTURE)) {
            $titleYear = (int) $matches[1][0];
            $title = substr($title, 0, $matches[0][1]);
        }
        $title = preg_replace_callback('/\((MULTI|FR|VOST ?FR|HD|FHD|UHD|4K|3D|Blu-Ray)\)/i', fn ($m) => $addTag($m[1]), $title);
        $title = preg_replace_callback('/\b(FHD|UHD|4K|HD|MULTI|Multi)\b/', fn ($m) => $addTag($m[1]), $title);
        $title = trim(preg_replace('/\s+/', ' ', $title));
        $title = preg_replace('/\s*-\s*$/', '', $title);

        return $this->parsedName = [
            'title' => $title != '' ? $title : $this->name,
            'year' => $titleYear,
            'tags' => $tags,
        ];
    }

    /**
     * "|FR| 13EME RUE HD" -> name "13EME RUE", tags ["HD"]
     */
    public function channelName(): array
    {
        $tags = [];
        $name = preg_replace('/^(\s*\|[^|]*\|\s*)+/', '', $this->name);
        $name = preg_replace_callback('/\s*\b(UHD|FHD|HD|SD|4K|HEVC|H265|50 ?FPS)\b|\s*\((EXCLUS|BACKUP|SECOURS)\)/i', function ($m) use (&$tags) {
            $tags[] = strtoupper($m[1] ?: $m[2]);
            return '';
        }, $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return ['name' => $name != '' ? $name : $this->name, 'tags' => array_values(array_unique($tags))];
    }

    /**
     * The TMDB data probably belongs to another title when its year is far from the year in the name
     */
    public function isDoubtful(): bool
    {
        $titleYear = $this->parsedName()['year'];
        return $titleYear && $this->year > 0 && abs($titleYear - $this->year) > 1;
    }

    /**
     * How to show the note, with the explanation shown when hovering the badge
     */
    public function rating(): array
    {
        $rating = $this->ratingParts();
        $rating['tooltip'] = $rating['label'] . ' : ' . $rating['line'];
        return $rating;
    }

    /**
     * Tier (a, b, c, low, none), badge text, label and details of the note
     */
    protected function ratingParts(): array
    {
        $format = fn ($note) => number_format($note, 1, ',', '');
        $votes = (int) $this->votes;
        $votesText = $votes > 0 ? number_format($votes, 0, ',', ' ') . ' ' . trans_choice('vote|votes', $votes) : '';

        if ($this->note <= 0) {
            return ['tier' => 'none', 'badge' => '—', 'label' => __('Not rated yet'),
                'sub' => __('No rating yet'), 'line' => __('No TMDB rating for now.'), 'good' => false, 'low' => false];
        }
        if ($votes > 0 && $votes < self::LOW_VOTES) {
            return ['tier' => 'low', 'badge' => $format($this->note) . '?', 'label' => __('Few votes'),
                'sub' => __('Only :votes', ['votes' => $votesText]),
                'line' => __('TMDB :note/10 from :votes: not reliable, counted as :score for sorting.',
                    ['note' => $format($this->note), 'votes' => $votesText, 'score' => $format($this->score)]),
                'good' => false, 'low' => true];
        }

        $score = $this->score > 0 ? $this->score : $this->note;
        $tier = $score >= self::SCORE_EXCELLENT ? 'a' : ($score >= self::SCORE_GOOD ? 'b' : 'c');
        return [
            'tier' => $tier,
            'badge' => $format($score),
            'label' => ['a' => __('Excellent'), 'b' => __('Good'), 'c' => __('Average')][$tier],
            'sub' => $votes > 0 ? $votesText : __('TMDB rating'),
            'line' => 'TMDB ' . $format($this->note) . '/10 · ' . ($votes > 0 ? $votesText : __('number of votes unknown')),
            'good' => $score >= self::SCORE_GOOD,
            'low' => false,
        ];
    }

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
