<?php

use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

function makeTicketCinemaSession(): CinemaSession
{
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
        'name' => 'Salle 6',
        'total_seats' => 120,
        'is_active' => true,
    ]);

    return CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => '2026-07-07 18:30:00',
        'price' => 12.00,
        'is_active' => true,
    ]);
}

test('ticket pdf download is rejected without a valid signature', function () {
    $session = makeTicketCinemaSession();

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 1,
        'status' => 'confirmed',
    ]);

    $this->get(route('tickets.show', ['reservation' => $reservation->id]))
        ->assertForbidden();
});

test('reservation ticket pdf uses the blade template and preserves ticket data', function () {
    $session = makeTicketCinemaSession();

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
        'status' => 'confirmed',
    ]);

    $firstTicket = Ticket::create([
        'reservation_id' => $reservation->id,
        'ticket_number' => 'TK-001-01',
    ]);

    Ticket::create([
        'reservation_id' => $reservation->id,
        'ticket_number' => 'TK-001-02',
    ]);

    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.tickets', Mockery::on(function (array $data) use ($reservation): bool {
            expect($data['tickets'])->toHaveCount(2);
            expect($data['tickets'][0]['cinema_name'])->toBe('La Grande Cinema');
            expect($data['tickets'][0]['room_name'])->toBe('Salle 6');
            expect($data['tickets'][0]['ticket_number'])->toBe('TK-001-01');
            expect($data['tickets'][0]['customer_name'])->toBe('Ada Lovelace');
            expect($data['tickets'][0]['customer_email'])->toBe('ada@example.com');
            expect($data['tickets'][0]['reservation_number'])->toBe($reservation->id);
            expect($data['tickets'][0]['qr_data_uri'])->toStartWith('data:image/png;base64,');
            expect($data['tickets'][1]['ticket_index'])->toBe(2);

            return true;
        }))
        ->andReturnSelf();

    Pdf::shouldReceive('setPaper')
        ->once()
        ->with('a4', 'portrait')
        ->andReturnSelf();

    Pdf::shouldReceive('output')
        ->once()
        ->andReturn('%PDF-1.4 fake');

    $response = $this->get(URL::temporarySignedRoute('tickets.show', now()->addDay(), [
        'reservation' => $reservation->id,
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'attachment; filename="reservation-'.$reservation->id.'-tickets.pdf"');
});

test('single ticket pdf uses the blade template and keeps the signed url flow', function () {
    $session = makeTicketCinemaSession();

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 1,
        'status' => 'confirmed',
    ]);

    $ticket = Ticket::create([
        'reservation_id' => $reservation->id,
        'ticket_number' => 'TK-001-01',
    ]);

    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.tickets', Mockery::on(function (array $data) use ($ticket): bool {
            expect($data['tickets'])->toHaveCount(1);
            expect($data['tickets'][0]['ticket_number'])->toBe($ticket->ticket_number);
            expect($data['tickets'][0]['room_name'])->toBe('Salle 6');
            expect($data['tickets'][0]['qr_data_uri'])->toStartWith('data:image/png;base64,');

            return true;
        }))
        ->andReturnSelf();

    Pdf::shouldReceive('setPaper')
        ->once()
        ->with('a4', 'portrait')
        ->andReturnSelf();

    Pdf::shouldReceive('output')
        ->once()
        ->andReturn('%PDF-1.4 fake');

    $response = $this->get(URL::temporarySignedRoute('tickets.single', now()->addDay(), [
        'ticket' => $ticket->uuid,
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition', 'attachment; filename="ticket-'.$ticket->uuid.'.pdf"');
});
