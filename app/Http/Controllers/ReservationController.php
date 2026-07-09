<?php

namespace App\Http\Controllers;

use App\Mail\ReservationVerificationCodeMail;
use App\Models\CinemaSession;
use App\Models\ReservationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            'verification_code' => str_pad(
                random_int(0, 999999),
                6,
                '0',
                STR_PAD_LEFT
            ),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($reservationRequest->email)
            ->send(new ReservationVerificationCodeMail($reservationRequest));

        $request->session()->put('reservation_request_id', $reservationRequest->id);

        return redirect()->route('reservation.verify.notice');
    }

    public function verifyNotice(Request $request): Response
    {
        $reservationRequest = ReservationRequest::find(
            $request->session()->get('reservation_request_id')
        );

        abort_unless($reservationRequest, 404);

        return Inertia::render('reservation/VerifyNotice', [
            'email' => $reservationRequest->email,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $reservationRequest = ReservationRequest::find($request->session()->get('reservation_request_id'));

        abort_unless($reservationRequest, 404);

        if (strtoupper($validated['code']) !== strtoupper($reservationRequest->verification_code)) {
            return back()->withErrors([
                'code' => 'Le code n’est pas bon.',
            ]);
        }

        $request->session()->forget('reservation_request_id');

        return redirect()->route('reservation.confirmed');
    }
}
