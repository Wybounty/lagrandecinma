<?php

namespace App\Http\Controllers;

use App\Mail\ReservationVerificationCodeMail;
use App\Models\CinemaSession;
use App\Models\Payments;
use App\Models\ReservationRequest;
use App\Services\SeatAvailabilityService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Affiche le formulaire de réservation.
     */
    public function create(
        CinemaSession $cinemaSession,
        SeatAvailabilityService $seatAvailabilityService,
    ): Response {
        $cinemaSession->load('movie', 'room');
        $cinemaSession->setAttribute(
            'available_seats',
            $seatAvailabilityService->availableSeats($cinemaSession),
        );

        return Inertia::render('reservation/Request', [
            'movie' => $cinemaSession->movie,
            'session' => $cinemaSession,
        ]);
    }

    /**
     * Crée une demande de réservation.
     */
    public function store(
        Request $request,
        SeatAvailabilityService $seatAvailabilityService,
    ): RedirectResponse {
        $validated = $request->validate([
            'cinema_session_id' => ['required', 'exists:cinema_sessions,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'quantity' => ['required', 'integer', 'min:1', 'max:255'],
        ]);

        $reservationRequest = DB::transaction(function () use (
            $validated,
            $seatAvailabilityService,
        ): ReservationRequest {
            $cinemaSession = CinemaSession::query()
                ->whereKey($validated['cinema_session_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $availableSeats = $seatAvailabilityService->availableSeats($cinemaSession);

            if ($validated['quantity'] > $availableSeats) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Il ne reste que %d place(s) disponible(s) pour cette séance.',
                        $availableSeats,
                    ),
                ]);
            }

            return ReservationRequest::create([
                ...$validated,
                'verification_code' => Str::padLeft((string) random_int(0, 999999), 6, '0'),
                'token' => Str::uuid(),
                'expires_at' => now()->addMinutes(15),
            ]);
        });

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
    public function verify(
        Request $request,
        string $token,
        SeatAvailabilityService $seatAvailabilityService,
        StripeCheckoutService $stripeCheckoutService,
    ) {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        return DB::transaction(function () use (
            $token,
            $validated,
            $seatAvailabilityService,
            $stripeCheckoutService,
        ) {
            $reservationRequest = ReservationRequest::query()
                ->where('token', $token)
                ->lockForUpdate()
                ->firstOrFail();

            if (strtoupper($validated['code']) !== strtoupper($reservationRequest->verification_code)) {
                return back()->withErrors([
                    'code' => 'Le code n’est pas bon.',
                ]);
            }

            if ($reservationRequest->completed_at !== null) {
                return Inertia::location(route('reservation.confirmed'));
            }

            if ($reservationRequest->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'code' => 'Votre demande de réservation a expiré. Veuillez recommencer.',
                ]);
            }

            $cinemaSession = CinemaSession::query()
                ->whereKey($reservationRequest->cinema_session_id)
                ->lockForUpdate()
                ->firstOrFail();
            $cinemaSession->loadMissing('movie', 'room');

            $availableSeats = $seatAvailabilityService->availableSeats(
                $cinemaSession,
                $reservationRequest,
            );

            if ($reservationRequest->quantity > $availableSeats) {
                throw ValidationException::withMessages([
                    'code' => sprintf(
                        'Il ne reste que %d place(s) disponible(s) pour cette séance.',
                        $availableSeats,
                    ),
                ]);
            }

            $payment = $reservationRequest->payment()->lockForUpdate()->first();

            if ($payment !== null && $payment->status === 'paid') {
                return Inertia::location(route('reservation.confirmed'));
            }

            $amount = (int) round($cinemaSession->price * $reservationRequest->quantity * 100);

            $payment ??= new Payments([
                'reservation_request_id' => $reservationRequest->id,
            ]);

            $payment->fill([
                'reservation_request_id' => $reservationRequest->id,
                'amount' => $amount,
                'currency' => 'eur',
                'status' => 'pending',
            ]);
            $payment->save();

            $checkout = $stripeCheckoutService->createSession(
                $payment,
                $cinemaSession->movie->title,
            );

            $payment->update([
                'stripe_checkout_session_id' => $checkout->id,
            ]);

            return Inertia::location($checkout->url);
        });
    }
}
