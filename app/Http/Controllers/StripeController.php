<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Payments;
use Illuminate\Support\Facades\DB;
use Stripe\Webhook;
use App\Models\CinemaSession;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\ReservationConfirmationMail;

class StripeController extends Controller
{
    public function success(Request $request): Response
    {
        return Inertia::render('reservation/Confirmed');
    }

    public function cancel(): Response
    {
        return Inertia::render('reservation/Cancel');
    }

    public function handle(Request $request)
    {
        $event = Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );

        if ($event->type === 'checkout.session.completed') {
            $stripeSession = $event->data->object;

            $payment = Payments::findOrFail(
                $stripeSession->metadata->payment_id
            );

            $reservation = DB::transaction(function () use ($payment, $stripeSession) {

                if ($payment->status === 'paid') {
                    return null;
                }

                $reservationRequest = $payment->reservationRequest;

                $cinemaSession = CinemaSession::where(
                    'id',
                    $reservationRequest->cinema_session_id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                // TODO : vérifier les places disponibles ici

                $reservation = Reservation::create([
                    'cinema_session_id' => $cinemaSession->id,
                    'first_name' => $reservationRequest->first_name,
                    'last_name' => $reservationRequest->last_name,
                    'email' => $reservationRequest->email,
                    'quantity' => $reservationRequest->quantity,
                    'status' => 'confirmed',
                ]);

                for ($i = 0; $i < $reservationRequest->quantity; $i++) {
                    Ticket::create([
                        'reservation_id' => $reservation->id,
                        'ticket_number' => sprintf(
                            'TK-%03d-%02d',
                            $reservation->id,
                            $i + 1
                        ),
                    ]);
                }

                $payment->update([
                    'status' => 'paid',
                    'stripe_payment_intent_id' => $stripeSession->payment_intent,
                    'paid_at' => now(),
                ]);

                return $reservation;
            });

            // Si le webhook avait déjà été traité
            if ($reservation === null) {
                return response()->json(['received' => true]);
            }

            // Génération du lien signé
            $ticketDownloadUrl = URL::temporarySignedRoute(
                'tickets.show',
                now()->addDays(7),
                ['reservation' => $reservation->id],
            );

            // Envoi du mail APRÈS la transaction
            Mail::to($reservation->email)
                ->send(new ReservationConfirmationMail(
                    $reservation->load('tickets'),
                    $ticketDownloadUrl,
                ));
        }

        return response()->json(['received' => true]);
    }
}