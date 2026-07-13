<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payments extends Model
{
    protected $fillable = [
        'reservation_request_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    /**
     * La demande de réservation associée à ce paiement.
     */
    public function reservationRequest(): BelongsTo
    {
        return $this->belongsTo(ReservationRequest::class);
    }
}