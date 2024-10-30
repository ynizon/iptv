<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Url extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public function isFavorite($user_id) {
        $view = View::where("url",$this->url)->where("user_id",$user_id)->first();
        return $view ? $view->favorite : 0;
    }

    public function isWatched($user_id) {
        $view = View::where("url",$this->url)->where("user_id",$user_id)->first();
        return $view ? $view->watched : 0;
    }

    public function counter($user_id) {
        $view = View::where("url",$this->url)->where("user_id",$user_id)->first();
        return $view ? $this->formatHour($view->counter) : $this->formatHour(0);
    }

    public function counterMin($user_id) {
        $view = View::where("url",$this->url)->where("user_id",$user_id)->first();
        return $view ? $view->counter : 0;
    }

    public function counterSec($user_id) {
        return $this->counterMin($user_id) * 60;
    }

    protected function formatHour(int $minutesTime) : string {
        if ($minutesTime == 0) {return '';}
        $hours = intdiv($minutesTime, 60);
        $minutes = $minutesTime % 60;
        if ($minutes <10){
            $minutes = "0".$minutes;
        }

        return $hours . ":" . $minutes;
    }
}
