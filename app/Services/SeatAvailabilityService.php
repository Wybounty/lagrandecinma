<?php

namespace App\Services;

use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\ReservationRequest;

class SeatAvailabilityService
{
    public function availableSeats(
        CinemaSession $cinemaSession,
        ?ReservationRequest $excludedReservationRequest = null,
    ): int {
        $cinemaSession->loadMissing('room');

        $confirmedSeats = (int) Reservation::query()
            ->where('cinema_session_id', $cinemaSession->id)
            ->where('status', 'confirmed')
            ->sum('quantity');

        $activeHoldQuery = ReservationRequest::query()
            ->where('cinema_session_id', $cinemaSession->id)
            ->whereNull('completed_at')
            ->where('expires_at', '>', now());

        if ($excludedReservationRequest !== null) {
            $activeHoldQuery->whereKeyNot($excludedReservationRequest->getKey());
        }

        $activeHoldSeats = (int) $activeHoldQuery->sum('quantity');

        return max(
            0,
            (int) $cinemaSession->room->total_seats - $confirmedSeats - $activeHoldSeats,
        );
    }
}
