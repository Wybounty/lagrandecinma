<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Movie extends Model
{
    /**
     * Les champs pouvant être remplis en masse.
     */
    protected $fillable = [
        'title',
        'description',
        'slug',
        'genre',
        'duration',
        'release_date',
        'poster',
        'trailer_url',
        'is_active',
    ];

    /**
     * Conversion automatique des types.
     */
    protected $casts = [
        'release_date' => 'date',
        'duration' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($movie) {
            $movie->slug = Str::slug($movie->title);
        });
    }

    /**
     * Get the cinema sessions for the movie.
     *
     * @return HasMany<CinemaSession, $this>
     */
    public function cinemaSessions(): HasMany
    {
        return $this->hasMany(CinemaSession::class);
    }
}
