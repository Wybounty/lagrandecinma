<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function createDashboardMovie(string $title): Movie
{
    return Movie::create([
        'title' => $title,
        'description' => 'Description du film.',
        'genre' => 'Genre',
        'duration' => 120,
        'release_date' => '2025-01-01',
        'poster' => 'movies/poster.jpg',
        'trailer_url' => 'https://example.com',
        'is_active' => true,
    ]);
}

function createDashboardSession(Movie $movie, string $roomName, int $totalSeats, string $startsAt): CinemaSession
{
    $room = Room::create([
        'name' => $roomName,
        'total_seats' => $totalSeats,
        'is_active' => true,
    ]);

    return CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt,
        'price' => 12.00,
        'is_active' => true,
    ]);
}

it('shows the dashboard statistics and top sessions', function () {
    $user = User::factory()->create();

    $movieOne = createDashboardMovie('Alpha');
    $movieTwo = createDashboardMovie('Beta');

    $sessionOne = createDashboardSession($movieOne, 'Salle 1', 20, now()->addDay()->setTime(18, 30));
    $sessionTwo = createDashboardSession($movieTwo, 'Salle 2', 30, now()->addDays(2)->setTime(20, 0));
    $sessionThree = createDashboardSession($movieTwo, 'Salle 3', 40, now()->subDay()->setTime(20, 0));

    Reservation::create([
        'cinema_session_id' => $sessionOne->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 5,
        'status' => 'confirmed',
    ]);

    Reservation::create([
        'cinema_session_id' => $sessionTwo->id,
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'quantity' => 4,
        'status' => 'confirmed',
    ]);

    Reservation::create([
        'cinema_session_id' => $sessionTwo->id,
        'first_name' => 'Linus',
        'last_name' => 'Torvalds',
        'email' => 'linus@example.com',
        'quantity' => 3,
        'status' => 'cancelled',
    ]);

    Reservation::create([
        'cinema_session_id' => $sessionThree->id,
        'first_name' => 'Cancelled',
        'last_name' => 'Session',
        'email' => 'cancelled@example.com',
        'quantity' => 10,
        'status' => 'cancelled',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('stats.confirmed_reservations_count', 2)
            ->where('stats.confirmed_seats_count', 9)
            ->where('stats.upcoming_sessions_count', 2)
            ->where('stats.movies_count', 2)
            ->where('fill_rate.percentage', 18)
            ->where('fill_rate.reserved_seats', 9)
            ->where('fill_rate.capacity', 50)
            ->has('top_sessions', 2)
            ->where('top_sessions.0.movie_title', 'Alpha')
            ->where('top_sessions.0.reserved_seats', 5)
            ->where('top_sessions.0.capacity', 20)
            ->where('top_sessions.0.fill_rate', 25)
            ->where('top_sessions.1.movie_title', 'Beta')
            ->where('top_sessions.1.reserved_seats', 4),
        );
});
