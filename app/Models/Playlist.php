<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
    ];

    public function urls(): HasMany
    {
        return $this->hasMany(Url::class);
    }
}
