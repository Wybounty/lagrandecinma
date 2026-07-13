<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Room;
use Inertia\Testing\AssertableInertia as Assert;

test('movie page is displayed', function () {
    $movie = Movie::create([
        'title' => 'Interstellar',
        'description' => 'Une équipe d\'explorateurs traverse un trou de ver afin de sauver l\'humanité.',
        'genre' => 'Science-fiction',
        'duration' => 169,
        'release_date' => '2014-11-05',
        'poster' => 'movies/interstellar.jpg',
        'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
        'is_active' => true,
    ]);

    $this->get(route('movies.show', ['movie' => $movie->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('movie/Show')
            ->where('movie.id', $movie->id)
            ->where('movie.title', $movie->title)
            ->where('movie.slug', $movie->slug)
            ->where('movie.description', $movie->description)
            ->where('movie.genre', $movie->genre)
            ->where('movie.duration', $movie->duration)
            ->where('movie.poster', $movie->poster)
            ->where('movie.trailer_url', $movie->trailer_url),
        );
});

test('movie page exposes the available seats for each session', function () {
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
        'name' => 'Salle 7',
        'total_seats' => 20,
        'is_active' => true,
    ]);

    $session = CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => now()->addDay()->setTime(20, 0),
        'price' => 12.00,
        'is_active' => true,
    ]);

    Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 6,
        'status' => 'confirmed',
    ]);

    ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'quantity' => 3,
        'verification_code' => 'ABC123',
        'token' => 'token-availability',
        'expires_at' => now()->addMinutes(15),
    ]);

    $this->get(route('movies.show', ['movie' => $movie->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('movie/Show')
            ->where('movie.cinema_sessions.0.available_seats', 11),
        );
});

test('movie page returns a 404 for an unknown slug', function () {
    $this->get(route('movies.show', ['movie' => 'unknown-movie']))
        ->assertNotFound();
});
