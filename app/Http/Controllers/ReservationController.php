<?php

namespace App\Http\Controllers;

use App\Mail\ReservationVerificationCodeMail;
use App\Models\CinemaSession;
use App\Models\ReservationRequest;
use App\Models\Reservation;
use App\Models\Ticket;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

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

        //$validated['token'] = Str::uuid();

        $reservationRequest = ReservationRequest::create([
            ...$validated,
            'verification_code' => str_pad(
                random_int(0, 999999),
                6,
                '0',
                STR_PAD_LEFT
            ),
            'token' => Str::uuid(),
            'expires_at' => now()->addMinutes(2),
        ]);

        Mail::to($reservationRequest->email)
            ->send(new ReservationVerificationCodeMail($reservationRequest));

        //$request->session()->put('reservation_request_id', $reservationRequest->id);

        return redirect()->route('reservation.verify.notice', [
            'token' => $reservationRequest->token,
        ]);
    }

    // On veux atterir sur la page de verification
    public function verifyNotice(Request $request): Response
    {
        $reservationRequest = ReservationRequest::where('token', $request->token)->firstOrFail();

        abort_unless($reservationRequest, 404);

        $countDown = $reservationRequest->expires_at;
        $email = $reservationRequest->email;

        return Inertia::render('reservation/VerifyNotice', [
            'token' => $reservationRequest->token,
            'expires_at' => $countDown,
            'email' => $email,
        ]);
    }

    public function verify(Request $request, string $token): RedirectResponse
    {

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $reservationRequest = ReservationRequest::where('token',$token)->firstOrFail();

        abort_unless($reservationRequest, 404);

        if (strtoupper($validated['code']) !== strtoupper($reservationRequest->verification_code)) {
            return back()->withErrors([
                'code' => 'Le code n’est pas bon.',
            ]);
        }

        // Créer une réservation

        $reservation = Reservation::create([
            'cinema_session_id' => $reservationRequest->cinema_session_id,
            'first_name' => $reservationRequest->first_name,
            'last_name' => $reservationRequest->last_name,
            'email' => $reservationRequest->email,
            'quantity' => $reservationRequest->quantity,
            'status' => 'confirmed',
        ]);

        // Sauvegarder les tickets

        // nb Ticket 
        for($i = 0; $i != $reservationRequest->quantity; $i++)
            {
                Ticket::create([
                    'reservation_id' => $reservation->id,
                    'ticket_number' => sprintf('TK-%03d-%02d', $reservation->id, $i + 1),
                ]);
            }

        

        return redirect()->route('reservation.confirmed');

    }
}
