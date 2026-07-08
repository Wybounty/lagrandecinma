<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ReservationRequest;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\CinemaSession;

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

        ReservationRequest::create([
            ...$validated,
            'verification_code' => str_pad(
                random_int(0, 999999),
                6,
                '0',
                STR_PAD_LEFT
            ),
            'expires_at' => now()->addMinutes(10),
        ]);

        // TODO :
        // Envoyer le code par mail

        return redirect()->route('reservation.verify.notice');
    }
}
