<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'total_seats',
        'is_active',
    ];

    /**
     * Get the cinema sessions scheduled in this room.
     *
     * @return HasMany<CinemaSession, $this>
     */
    public function cinemaSessions(): HasMany
    {
        return $this->hasMany(CinemaSession::class);
    }
}
