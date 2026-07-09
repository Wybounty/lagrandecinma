<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Room;
use App\Models\Ticket;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Carbon;

test('reservation models are related correctly', function () {
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
        'price' => 12.00,
        'is_active' => true,
    ]);

    $reservationRequest = ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'quantity' => 2,
        'verification_code' => 'ABC123',
        'token' => 'token-123',
        'expires_at' => Carbon::parse('2026-07-07 12:00:00'),
    ]);

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'quantity' => 2,
        'status' => 'confirmed',
    ]);

    $ticket = Ticket::create([
        'reservation_id' => $reservation->id,
        'ticket_number' => 'TK-999-01',
    ]);

    expect($reservationRequest->cinemaSession->is($session))->toBeTrue();
    expect($reservation->cinemaSession->is($session))->toBeTrue();
    expect($reservation->tickets)->toHaveCount(1);
    expect($ticket->reservation->is($reservation))->toBeTrue();
});

test('database seeder creates reservation requests reservations and tickets', function () {
    $this->seed(DatabaseSeeder::class);

    expect(ReservationRequest::count())->toBe(5);
    expect(Reservation::count())->toBe(4);
    expect(Ticket::count())->toBe(7);

    expect(Reservation::query()->where('status', 'confirmed')->count())->toBe(3);

    foreach (Reservation::query()->where('status', 'confirmed')->get() as $reservation) {
        expect($reservation->tickets)->toHaveCount($reservation->quantity);
    }

    $this->assertDatabaseHas('reservation_requests', [
        'verification_code' => 'A1B2C3',
    ]);

    $this->assertDatabaseHas('reservations', [
        'status' => 'cancelled',
    ]);

    $this->assertDatabaseHas('tickets', [
        'ticket_number' => 'TK-001-01',
    ]);
});
