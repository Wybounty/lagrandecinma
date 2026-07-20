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
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ReservationController extends Controller
{
    /**
     * Affiche le formulaire de rÃ©servation.
     */
    public function create(
        CinemaSession $cinemaSession,
        SeatAvailabilityService $seatAvailabilityService,
    ): Response|RedirectResponse {
        $cinemaSession->loadMissing('movie', 'room');

        if (! $this->isReservable($cinemaSession, $seatAvailabilityService)) {
            return $this->redirectToMovie($cinemaSession);
        }

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
     * CrÃ©e une demande de rÃ©servation.
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

        $result = DB::transaction(function () use (
            $validated,
            $seatAvailabilityService,
        ): ReservationRequest|RedirectResponse {
            $cinemaSession = CinemaSession::query()
                ->with('movie', 'room')
                ->whereKey($validated['cinema_session_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->isReservable($cinemaSession, $seatAvailabilityService)) {
                return $this->redirectToMovie($cinemaSession);
            }

            $availableSeats = $seatAvailabilityService->availableSeats($cinemaSession);

            if ($validated['quantity'] > $availableSeats) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Il ne reste que %d place(s) disponible(s) pour cette sÃ©ance.',
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

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        Mail::to($result->email)
            ->send(new ReservationVerificationCodeMail($result));

        return redirect()->route('reservation.verify.notice', [
            'token' => $result->token,
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
     * VÃ©rifie le code de vÃ©rification et crÃ©e la rÃ©servation si le code est correct.
     */
    public function verify(
        Request $request,
        string $token,
        SeatAvailabilityService $seatAvailabilityService,
        StripeCheckoutService $stripeCheckoutService,
    ): HttpFoundationResponse {
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
                    'code' => 'Le code nâ€™est pas bon.',
                ]);
            }

            if ($reservationRequest->completed_at !== null) {
                return Inertia::location(route('reservation.confirmed'));
            }

            if ($reservationRequest->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'code' => 'Votre demande de rÃ©servation a expirÃ©. Veuillez recommencer.',
                ]);
            }

            $cinemaSession = CinemaSession::query()
                ->with('movie', 'room')
                ->whereKey($reservationRequest->cinema_session_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->isReservable($cinemaSession, $seatAvailabilityService)) {
                return Inertia::location(route('movies.show', $cinemaSession->movie));
            }

            $availableSeats = $seatAvailabilityService->availableSeats(
                $cinemaSession,
                $reservationRequest,
            );

            if ($reservationRequest->quantity > $availableSeats) {
                throw ValidationException::withMessages([
                    'code' => sprintf(
                        'Il ne reste que %d place(s) disponible(s) pour cette sÃ©ance.',
                        $availableSeats,
                    ),
                ]);
            }

            /** @var Payments|null $payment */
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

    private function isReservable(
        CinemaSession $cinemaSession,
        SeatAvailabilityService $seatAvailabilityService,
    ): bool {
        $cinemaSession->loadMissing('movie', 'room');

        if (! $cinemaSession->is_active) {
            return false;
        }

        if (! $cinemaSession->starts_at->isFuture()) {
            return false;
        }

        return $seatAvailabilityService->availableSeats($cinemaSession) > 0;
    }

    private function redirectToMovie(CinemaSession $cinemaSession): RedirectResponse
    {
        $cinemaSession->loadMissing('movie');

        return redirect()->route('movies.show', $cinemaSession->movie);
    }
}
