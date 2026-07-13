<?php

use App\Mail\ReservationConfirmationMail;
use App\Models\CinemaSession;
use App\Models\Movie;
use App\Models\Payments;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Room;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;

function makeStripeSession(): CinemaSession
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
        'total_seats' => 12,
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

test('stripe webhook creates a reservation only once for a completed checkout session', function () {
    Mail::fake();

    $session = makeStripeSession();

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

    $payment = Payments::create([
        'reservation_request_id' => $reservationRequest->id,
        'stripe_checkout_session_id' => 'cs_test_123',
        'amount' => 2400,
        'currency' => 'eur',
        'status' => 'pending',
    ]);

    $event = (object) [
        'type' => 'checkout.session.completed',
        'data' => (object) [
            'object' => (object) [
                'id' => 'cs_test_123',
                'payment_intent' => 'pi_test_123',
                'metadata' => (object) [
                    'payment_id' => (string) $payment->id,
                ],
            ],
        ],
    ];

    $webhook = Mockery::mock('alias:Stripe\Webhook');
    $webhook->shouldReceive('constructEvent')
        ->twice()
        ->andReturn($event);

    $response = $this->post(route('stripe.webhook'), [], [
        'Stripe-Signature' => 'test-signature',
    ]);

    $response->assertOk()->assertJson(['received' => true]);

    expect(Reservation::count())->toBe(1);
    expect(Ticket::count())->toBe(2);
    expect(Payments::query()->first()->status)->toBe('paid');
    expect(ReservationRequest::query()->first()->completed_at)->not->toBeNull();

    $this->post(route('stripe.webhook'), [], [
        'Stripe-Signature' => 'test-signature',
    ])->assertOk();

    expect(Reservation::count())->toBe(1);
    expect(Ticket::count())->toBe(2);

    Mail::assertSent(ReservationConfirmationMail::class);
});
