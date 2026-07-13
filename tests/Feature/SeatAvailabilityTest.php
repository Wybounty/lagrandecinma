<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Room;
use App\Services\SeatAvailabilityService;

function makeAvailabilitySession(int $totalSeats = 10): CinemaSession
{
    $movie = Movie::create([
        'title' => 'Interstellar',
        'description' => 'Une Ã©quipe d\'explorateurs traverse un trou de ver afin de sauver l\'humanitÃ©.',
        'genre' => 'Science-fiction',
        'duration' => 169,
        'release_date' => '2014-11-05',
        'poster' => 'movies/interstellar.jpg',
        'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
        'is_active' => true,
    ]);

    $room = Room::create([
        'name' => 'Salle 1',
        'total_seats' => $totalSeats,
        'is_active' => true,
    ]);

    return CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => now()->addDay()->setTime(18, 30),
        'price' => 12.00,
        'is_active' => true,
    ]);
}

test('available seats exclude confirmed reservations and active holds', function () {
    $session = makeAvailabilitySession(10);

    Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 3,
        'status' => 'confirmed',
    ]);

    ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'quantity' => 2,
        'verification_code' => 'ABC123',
        'token' => 'token-active',
        'expires_at' => now()->addMinutes(15),
    ]);

    ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Expired',
        'last_name' => 'Hold',
        'email' => 'expired@example.com',
        'quantity' => 4,
        'verification_code' => 'XYZ789',
        'token' => 'token-expired',
        'expires_at' => now()->subMinute(),
    ]);

    $availableSeats = app(SeatAvailabilityService::class)->availableSeats($session);

    expect($availableSeats)->toBe(5);
});

test('available seats can exclude the current hold during verification', function () {
    $session = makeAvailabilitySession(10);

    Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 3,
        'status' => 'confirmed',
    ]);

    $currentRequest = ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'quantity' => 2,
        'verification_code' => 'ABC123',
        'token' => 'token-current',
        'expires_at' => now()->addMinutes(15),
    ]);

    ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Other',
        'last_name' => 'Hold',
        'email' => 'other@example.com',
        'quantity' => 1,
        'verification_code' => 'XYZ789',
        'token' => 'token-other',
        'expires_at' => now()->addMinutes(15),
    ]);

    $availableSeats = app(SeatAvailabilityService::class)->availableSeats(
        $session,
        $currentRequest,
    );

    expect($availableSeats)->toBe(6);
});
