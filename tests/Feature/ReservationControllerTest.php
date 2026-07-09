<?php

use App\Mail\ReservationConfirmationMail;
use App\Mail\ReservationVerificationCodeMail;
use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Room;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

function makeCinemaSession(): CinemaSession
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
        'name' => 'Salle 1',
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

test('reservation request is created and verification mail is sent', function () {
    Mail::fake();

    $session = makeCinemaSession();

    $response = $this->post(route('reservation.store'), [
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
    ]);

    $response->assertRedirect();

    $reservationRequest = ReservationRequest::query()->first();

    expect($reservationRequest)->not->toBeNull();
    expect($reservationRequest->verification_code)->toMatch('/^\d{6}$/');
    expect($reservationRequest->token)->not->toBeEmpty();
    expect($reservationRequest->expires_at)->not->toBeNull();

    Mail::assertSent(ReservationVerificationCodeMail::class);
    Mail::assertNotSent(ReservationConfirmationMail::class);
});

test('reservation verification page is displayed for a token', function () {
    $session = makeCinemaSession();

    $reservationRequest = ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
        'verification_code' => '123456',
        'token' => 'token-123',
        'expires_at' => now()->addMinutes(2),
    ]);

    $this->get(route('reservation.verify.notice', ['token' => $reservationRequest->token]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('reservation/VerifyNotice')
            ->where('token', $reservationRequest->token)
            ->where('email', $reservationRequest->email),
        );
});

test('reservation is confirmed when the verification code matches', function () {
    Mail::fake();

    $session = makeCinemaSession();

    $reservationRequest = ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
        'verification_code' => 'ABC123',
        'token' => 'token-123',
        'expires_at' => now()->addMinutes(2),
    ]);

    $response = $this->post(route('reservation.verify', ['token' => $reservationRequest->token]), [
        'code' => 'abc123',
    ]);

    $response->assertRedirect(route('reservation.confirmed'));

    expect(Reservation::query()->count())->toBe(1);
    expect(Ticket::query()->count())->toBe(2);

    $reservation = Reservation::query()->first();

    expect($reservation->status)->toBe('confirmed');
    expect($reservation->tickets)->toHaveCount(2);
    expect($reservation->tickets->first()->uuid)->not->toBeEmpty();

    Mail::assertSent(ReservationConfirmationMail::class);
});

test('reservation verification is rejected when the verification code does not match', function () {
    $session = makeCinemaSession();

    $reservationRequest = ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
        'verification_code' => 'ABC123',
        'token' => 'token-123',
        'expires_at' => now()->addMinutes(2),
    ]);

    $response = $this->from(route('reservation.verify.notice', ['token' => $reservationRequest->token]))
        ->post(route('reservation.verify', ['token' => $reservationRequest->token]), [
            'code' => 'wrong1',
        ]);

    $response->assertRedirect(route('reservation.verify.notice', ['token' => $reservationRequest->token]));
    $response->assertSessionHasErrors(['code']);

    expect(Reservation::count())->toBe(0);
    expect(Ticket::count())->toBe(0);
});

test('signed ticket url returns a pdf', function () {
    $session = makeCinemaSession();

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 1,
        'status' => 'confirmed',
    ]);

    $response = $this->get(URL::temporarySignedRoute('tickets.show', now()->addDay(), [
        'reservation' => $reservation->id,
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('reservation pdf contains one page per ticket when downloading all tickets', function () {
    $session = makeCinemaSession();

    $reservation = Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 4,
        'status' => 'confirmed',
    ]);

    for ($i = 1; $i <= 4; $i++) {
        Ticket::create([
            'reservation_id' => $reservation->id,
            'ticket_number' => sprintf('TK-%03d-%02d', $reservation->id, $i),
        ]);
    }

    $response = $this->get(URL::temporarySignedRoute('tickets.show', now()->addDay(), [
        'reservation' => $reservation->id,
    ]));

    $response->assertOk();

    $pdf = $response->getContent();

    expect($pdf)->toContain('%PDF-1.4');
    expect($pdf)->toContain('Billet 1');
    expect($pdf)->toContain('Billet 2');
    expect($pdf)->toContain('Billet 3');
    expect($pdf)->toContain('Billet 4');
});

test('single ticket download url is signed and unique per ticket', function () {
    $session = makeCinemaSession();

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

    $secondTicket = Ticket::create([
        'reservation_id' => $reservation->id,
        'ticket_number' => 'TK-001-02',
    ]);

    $firstUrl = URL::temporarySignedRoute('tickets.single', now()->addDay(), [
        'ticket' => $firstTicket->uuid,
    ]);

    $secondUrl = URL::temporarySignedRoute('tickets.single', now()->addDay(), [
        'ticket' => $secondTicket->uuid,
    ]);

    expect($firstUrl)->not->toBe($secondUrl);
    expect($firstUrl)->toContain($firstTicket->uuid);
    expect($secondUrl)->toContain($secondTicket->uuid);
});
