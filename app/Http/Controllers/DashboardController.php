<?php

namespace App\Http\Controllers;

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $confirmedReservationsQuery = Reservation::query()
            ->where('status', 'confirmed');

        $confirmedReservationsCount = (clone $confirmedReservationsQuery)->count();
        $confirmedSeatsCount = (clone $confirmedReservationsQuery)->sum('quantity');
        $upcomingSessionsCount = CinemaSession::query()
            ->where('starts_at', '>', now())
            ->count();
        $moviesCount = Movie::query()->count();

        $sessionsWithReservations = CinemaSession::query()
            ->select('cinema_sessions.*')
            ->with(['movie', 'room'])
            ->withSum([
                'reservations as confirmed_reserved_seats' => fn ($query) => $query->where('status', 'confirmed'),
            ], 'quantity')
            ->whereHas('reservations', fn ($query) => $query->where('status', 'confirmed'))
            ->orderByDesc('confirmed_reserved_seats')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $sessionsTotalCapacity = (int) $sessionsWithReservations->sum(
            fn (CinemaSession $session): int => (int) $session->room->total_seats,
        );
        $globalFillRate = $sessionsTotalCapacity > 0
            ? round(($confirmedSeatsCount / $sessionsTotalCapacity) * 100, 1)
            : 0.0;

        $topSessions = $sessionsWithReservations->map(function (CinemaSession $session): array {
            $reservedSeats = (int) ($session->confirmed_reserved_seats ?? 0);
            $capacity = (int) $session->room->total_seats;

            return [
                'id' => $session->id,
                'movie_title' => $session->movie->title,
                'starts_at' => $session->starts_at->toIso8601String(),
                'room_name' => $session->room->name,
                'reserved_seats' => $reservedSeats,
                'capacity' => $capacity,
                'fill_rate' => $capacity > 0 ? round(($reservedSeats / $capacity) * 100, 1) : 0.0,
            ];
        })->values();

        return Inertia::render('dashboard', [
            'stats' => [
                'confirmed_reservations_count' => $confirmedReservationsCount,
                'confirmed_seats_count' => $confirmedSeatsCount,
                'upcoming_sessions_count' => $upcomingSessionsCount,
                'movies_count' => $moviesCount,
            ],
            'fill_rate' => [
                'percentage' => $globalFillRate,
                'reserved_seats' => $confirmedSeatsCount,
                'capacity' => $sessionsTotalCapacity,
            ],
            'top_sessions' => $topSessions,
        ]);
    }
}
