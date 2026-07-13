<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function createAdminMovie(): Movie
{
    return Movie::create([
        'title' => 'Interstellar',
        'description' => 'Une equipe traverse un trou de ver pour sauver l humanite.',
        'genre' => 'Science-fiction',
        'duration' => 169,
        'release_date' => '2014-11-05',
        'poster' => 'movies/interstellar.jpg',
        'trailer_url' => 'https://www.youtube.com/watch?v=zSWdZVtXT7E',
        'is_active' => true,
    ]);
}

function createAdminSession(Movie $movie, int $totalSeats = 20): CinemaSession
{
    $room = Room::create([
        'name' => 'Salle 1',
        'total_seats' => $totalSeats,
        'is_active' => true,
    ]);

    return CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => now()->addDay()->setTime(20, 0),
        'price' => 12.00,
        'is_active' => true,
    ]);
}

it('displays the admin movies index', function () {
    $user = User::factory()->create();
    $movie = createAdminMovie();

    $this->actingAs($user)
        ->get('/admin/movies')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/movies/Index'));

    expect(Movie::query()->whereKey($movie->id)->exists())->toBeTrue();
});

it('creates a movie with a generated slug', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/movies', [
            'title' => 'Dune',
            'description' => 'Une quete sur Arrakis.',
            'genre' => 'Science-fiction',
            'duration' => 155,
            'release_date' => '2025-07-12',
            'poster' => 'movies/dune.jpg',
            'trailer_url' => 'https://www.youtube.com/watch?v=abc123',
            'is_active' => true,
        ])
        ->assertRedirect('/admin/movies');

    $movie = Movie::query()->where('title', 'Dune')->firstOrFail();

    expect($movie->slug)->toBe('dune');
});

it('blocks movie deletion when sessions already exist', function () {
    $user = User::factory()->create();
    $movie = createAdminMovie();
    createAdminSession($movie);

    $this->actingAs($user)
        ->delete("/admin/movies/{$movie->id}")
        ->assertRedirect('/admin/movies')
        ->assertSessionHas('error');

    expect(Movie::query()->whereKey($movie->id)->exists())->toBeTrue();
});

it('creates a reservation and generates tickets in the admin area', function () {
    $user = User::factory()->create();
    $movie = createAdminMovie();
    $session = createAdminSession($movie, 30);

    $this->actingAs($user)
        ->post('/admin/reservations', [
            'cinema_session_id' => $session->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'quantity' => 2,
            'status' => 'confirmed',
        ])
        ->assertRedirect('/admin/reservations');

    $reservation = Reservation::query()->firstOrFail();

    expect($reservation->status)->toBe('confirmed');
    expect($reservation->tickets()->count())->toBe(2);
});

it('cancels a reservation instead of deleting it', function () {
    $user = User::factory()->create();
    $movie = createAdminMovie();
    $session = createAdminSession($movie, 30);

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'quantity' => 2,
        'status' => 'confirmed',
    ]);

    $this->actingAs($user)
        ->delete("/admin/reservations/{$reservation->id}")
        ->assertRedirect('/admin/reservations');

    expect($reservation->fresh()->status)->toBe('cancelled');
});

it('blocks session deletion when a reservation exists', function () {
    $user = User::factory()->create();
    $movie = createAdminMovie();
    $session = createAdminSession($movie, 30);

    Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@example.com',
        'quantity' => 1,
        'status' => 'confirmed',
    ]);

    $this->actingAs($user)
        ->delete("/admin/sessions/{$session->id}")
        ->assertRedirect('/admin/sessions')
        ->assertSessionHas('error');

    expect(CinemaSession::query()->whereKey($session->id)->exists())->toBeTrue();
});
