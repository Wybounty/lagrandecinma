<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmationMail;
use App\Mail\ReservationVerificationCodeMail;
use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Ticket;
use App\Models\Payments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Affiche le formulaire de réservation.
     */
    public function create(CinemaSession $cinemaSession): Response
    {
        $cinemaSession->load('movie', 'room');

        return Inertia::render('reservation/Request', [
            'movie' => $cinemaSession->movie,
            'session' => $cinemaSession,
        ]);
    }

    /**
     * Crée une demande de réservation.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cinema_session_id' => ['required', 'exists:cinema_sessions,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $reservationRequest = ReservationRequest::create([
            ...$validated,
            'verification_code' => Str::padLeft((string) random_int(0, 999999), 6, '0'),
            'token' => Str::uuid(),
            'expires_at' => now()->addMinutes(2),
        ]);

        Mail::to($reservationRequest->email)
            ->send(new ReservationVerificationCodeMail($reservationRequest));

        return redirect()->route('reservation.verify.notice', [
            'token' => $reservationRequest->token,
        ]);
    }

    /**
     * On veut atterir sur la page de verification.
     */
    public function verifyNotice(Request $request): Response
    {
        $reservationRequest = ReservationRequest::where('token', $request->token)->firstOrFail();

        return Inertia::render('reservation/VerifyNotice', [
            'token' => $reservationRequest->token,
            'expires_at' => $reservationRequest->expires_at,
            'email' => $reservationRequest->email,
        ]);
    }

    /**
     * Vérifie le code de vérification et crée la réservation si le code est correct.
     */

    public function verify(Request $request, string $token)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $reservationRequest = ReservationRequest::where('token', $token)->firstOrFail();

        if (strtoupper($validated['code']) !== strtoupper($reservationRequest->verification_code)) {
            return back()->withErrors([
                'code' => 'Le code n’est pas bon.',
            ]);
        }

        $amount = (int) round($reservationRequest->cinemaSession->price * $reservationRequest->quantity * 100);

        //dd($amount);


        $payment = Payments::create([
            'reservation_request_id' => $reservationRequest->id,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $checkout = $stripe->checkout->sessions->create([
            'mode' => 'payment',

            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => route('stripe.cancel'),
            
            'metadata' => [
                'payment_id' => (string) $payment->id,
            ],

            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $amount,

                    'product_data' => [
                        'name' => $reservationRequest->cinemaSession->movie->title,
                    ],
                ],

                'quantity' => 1,
            ]],
        ]);

        $payment->update([
            'stripe_checkout_session_id' => $checkout->id,
        ]);

        return Inertia::location($checkout->url);
    }
}
