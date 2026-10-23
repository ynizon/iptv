<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

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
     * Forget the cached groups, to call when urls, filters or renames change
     */
    public static function clearCache(): void
    {
        Cache::forget("category_groups");
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
