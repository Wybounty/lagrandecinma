<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReservationRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'token',
        'cinema_session_id',
        'first_name',
        'last_name',
        'email',
        'quantity',
        'verification_code',
        'expires_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token' => 'string',
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the cinema session for this reservation request.
     *
     * @return BelongsTo<CinemaSession, $this>
     */
    public function cinemaSession(): BelongsTo
    {
        return $this->belongsTo(CinemaSession::class);
    }

    /**
     * @return HasOne<Payments, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payments::class);
    }
}
