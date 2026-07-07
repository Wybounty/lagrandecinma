<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    /**
     * Les champs pouvant être remplis en masse.
     */
    protected $fillable = [
        'title',
        'description',
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

    /**
     * Relations
     */

}