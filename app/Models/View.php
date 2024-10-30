<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class View extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'url',
        'user_id',
        'watched',
        'favorite',
    ];

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
