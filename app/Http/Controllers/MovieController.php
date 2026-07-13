<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Services\SeatAvailabilityService;
use Inertia\Inertia;
use Inertia\Response;

class MovieController extends Controller
{
    public function show(
        Movie $movie,
        SeatAvailabilityService $seatAvailabilityService,
    ): Response {
        $movie->load([
            'cinemaSessions' => fn ($query) => $query->where('starts_at', '>', now())
                ->orderBy('starts_at')
                ->with('room'),
        ]);

        $movie->cinemaSessions->each(
            fn ($cinemaSession) => $cinemaSession->setAttribute(
                'available_seats',
                $seatAvailabilityService->availableSeats($cinemaSession),
            ),
        );

        return Inertia::render('movie/Show', [
            'movie' => $movie,
        ]);
    }
}
