<?php

use App\Mail\ReservationConfirmationMail;
use App\Mail\ReservationVerificationCodeMail;
use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Payments;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Room;
use App\Models\Ticket;
use App\Services\StripeCheckoutService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Stripe\Checkout\Session;

function makeCinemaSession(int $totalSeats = 120, ?string $startsAt = null): CinemaSession
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
        'total_seats' => $totalSeats,
        'is_active' => true,
    ]);

    return CinemaSession::create([
        'movie_id' => $movie->id,
        'room_id' => $room->id,
        'starts_at' => $startsAt ?? now()->addDays(7)->setTime(18, 30)->format('Y-m-d H:i:s'),
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
    expect($reservationRequest->expires_at->greaterThan(now()))->toBeTrue();

    Mail::assertSent(ReservationVerificationCodeMail::class);
    Mail::assertNotSent(ReservationConfirmationMail::class);
});

test('reservation request creation is rejected when not enough seats remain', function () {
    $session = makeCinemaSession(5);

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
        'quantity' => 1,
        'verification_code' => 'ABC123',
        'token' => 'token-hold-1',
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->from(route('reservation.create', ['cinemaSession' => $session->id]))
        ->post(route('reservation.store'), [
            'cinema_session_id' => $session->id,
            'first_name' => 'Linus',
            'last_name' => 'Torvalds',
            'email' => 'linus@example.com',
            'quantity' => 2,
        ]);

    $response->assertRedirect(route('reservation.create', ['cinemaSession' => $session->id]));
    $response->assertSessionHasErrors(['quantity']);

    expect(ReservationRequest::count())->toBe(1);
});

test('reservation creation page exposes the available seats', function () {
    $session = makeCinemaSession(8);

    Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 3,
        'status' => 'confirmed',
    ]);

    $this->get(route('reservation.create', ['cinemaSession' => $session->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('reservation/Request')
            ->where('session.available_seats', 5),
        );
});

test('reservation creation page redirects to the movie when the session is sold out', function () {
    $session = makeCinemaSession(2);
    $session->load('movie');

    Reservation::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
        'status' => 'confirmed',
    ]);

    $this->get(route('reservation.create', ['cinemaSession' => $session->id]))
        ->assertRedirect(route('movies.show', ['movie' => $session->movie->slug]));
});

test('reservation creation page redirects to the movie when the session is in the past', function () {
    $session = makeCinemaSession(8, now()->subDay()->setTime(18, 30)->format('Y-m-d H:i:s'));
    $session->load('movie');

    $this->get(route('reservation.create', ['cinemaSession' => $session->id]))
        ->assertRedirect(route('movies.show', ['movie' => $session->movie->slug]));
});

test('reservation request creation is redirected to the movie when the session is in the past', function () {
    Mail::fake();

    $session = makeCinemaSession(8, now()->subDay()->setTime(18, 30)->format('Y-m-d H:i:s'));
    $session->load('movie');

    $this->post(route('reservation.store'), [
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 1,
    ])->assertRedirect(route('movies.show', ['movie' => $session->movie->slug]));

    expect(ReservationRequest::count())->toBe(0);
    Mail::assertNotSent(ReservationVerificationCodeMail::class);
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

    $checkoutSession = Session::constructFrom([
        'id' => 'cs_test_checkout',
        'url' => 'https://checkout.stripe.test/session',
    ], null);

    $stripeCheckoutService = Mockery::mock(StripeCheckoutService::class);
    $stripeCheckoutService->shouldReceive('createSession')
        ->with(Mockery::type(Payments::class), 'Interstellar')
        ->andReturn($checkoutSession);

    $this->instance(StripeCheckoutService::class, $stripeCheckoutService);

    $response = $this->post(route('reservation.verify', ['token' => $reservationRequest->token]), [
        'code' => 'abc123',
    ]);

    $response->assertRedirect('https://checkout.stripe.test/session');

    expect(Reservation::query()->count())->toBe(0);
    expect(Ticket::query()->count())->toBe(0);
    expect(Payments::query()->count())->toBe(1);
    expect($reservationRequest->fresh()->completed_at)->toBeNull();

    Mail::assertNotSent(ReservationConfirmationMail::class);
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

test('reservation verification reuses an existing open checkout session without duplicating payments', function () {
    $session = makeCinemaSession();

    $reservationRequest = ReservationRequest::create([
        'cinema_session_id' => $session->id,
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@example.com',
        'quantity' => 2,
        'verification_code' => 'ABC123',
        'token' => 'token-123',
        'expires_at' => now()->addMinutes(15),
    ]);

    Payments::create([
        'reservation_request_id' => $reservationRequest->id,
        'stripe_checkout_session_id' => 'cs_test_existing',
        'amount' => 2400,
        'currency' => 'eur',
        'status' => 'pending',
    ]);

    $checkoutSession = Session::constructFrom([
        'id' => 'cs_test_existing',
        'status' => 'open',
        'url' => 'https://checkout.stripe.test/session',
    ], null);

    $stripeCheckoutService = Mockery::mock(StripeCheckoutService::class);
    $stripeCheckoutService->shouldReceive('createSession')
        ->with(Mockery::type(Payments::class), 'Interstellar')
        ->andReturn($checkoutSession);

    $this->instance(StripeCheckoutService::class, $stripeCheckoutService);

    $response = $this->post(route('reservation.verify', ['token' => $reservationRequest->token]), [
        'code' => 'abc123',
    ]);

    $response->assertRedirect('https://checkout.stripe.test/session');

    expect(Payments::count())->toBe(1);

    $this->post(route('reservation.verify', ['token' => $reservationRequest->token]), [
        'code' => 'abc123',
    ])->assertRedirect('https://checkout.stripe.test/session');

    expect(Payments::count())->toBe(1);
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
