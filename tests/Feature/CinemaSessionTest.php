<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;

test('rooms and cinema sessions can be related', function () {
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

    $room = Room::create([
        'name' => 'Salle 1',
        'total_seats' => 120,
        'is_active' => true,
    ]);

    $session = CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => '2026-07-07 18:30:00',
        'price' => 12.50,
        'is_active' => true,
    ]);

    expect($session->movie)->toBeInstanceOf(Movie::class);
    expect($session->movie->is($movie))->toBeTrue();
    expect($session->room)->toBeInstanceOf(Room::class);
    expect($session->room->is($room))->toBeTrue();
    expect($movie->cinemaSessions)->toHaveCount(1);
    expect($movie->cinemaSessions->first()->is($session))->toBeTrue();
    expect($room->cinemaSessions)->toHaveCount(1);
    expect($room->cinemaSessions->first()->is($session))->toBeTrue();
});

test('database seeder seeds rooms and five cinema sessions per movie', function () {
    $this->seed(DatabaseSeeder::class);

    $movies = Movie::query()->orderBy('id')->get();

    expect(Room::count())->toBe(3);
    expect(CinemaSession::count())->toBe($movies->count() * 5);

    foreach ($movies as $movie) {
        $sessions = CinemaSession::query()
            ->whereBelongsTo($movie)
            ->orderBy('starts_at')
            ->get();

        expect($sessions)->toHaveCount(5);
        expect($sessions->first()->starts_at)->toBeInstanceOf(CarbonImmutable::class);
        expect(
            $sessions->first()->starts_at->diffInDays($sessions->last()->starts_at)
        )->toBeLessThanOrEqual(14);
    }

    $this->assertDatabaseHas('rooms', [
        'name' => 'Salle VIP',
        'total_seats' => 40,
        'is_active' => true,
    ]);

    $rooms = Room::query()
        ->with(['cinemaSessions.movie'])
        ->orderBy('id')
        ->get();

    foreach ($rooms as $room) {
        $previousSession = null;

        foreach ($room->cinemaSessions->sortBy('starts_at') as $session) {
            if ($previousSession === null) {
                $previousSession = $session;

                continue;
            }

            $previousEnd = $previousSession->starts_at->addMinutes(
                $previousSession->movie->duration + 30
            );

            expect($session->starts_at->greaterThanOrEqualTo($previousEnd))->toBeTrue();

            $previousSession = $session;
        }
    }
});
