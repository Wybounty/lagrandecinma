<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmationMail;
use App\Models\CinemaSession;
use App\Models\Payments;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Ticket;
use App\Services\SeatAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Stripe\Webhook;

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

    public function handle(
        Request $request,
        SeatAvailabilityService $seatAvailabilityService,
    ): HttpFoundationResponse {
        $event = Webhook::constructEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret'),
        );

        if ($event->type === 'checkout.session.expired') {
            /** @var object{id: string} $stripeSession */
            $stripeSession = $event->data->object;

            DB::transaction(function () use ($stripeSession): void {
                $payment = Payments::query()
                    ->where('stripe_checkout_session_id', $stripeSession->id)
                    ->lockForUpdate()
                    ->first();

                if ($payment === null || $payment->status === 'paid') {
                    return;
                }

                $payment->update([
                    'status' => 'expired',
                ]);
            });

            return response()->json(['received' => true]);
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var object{metadata: object{payment_id: string}, payment_intent: string} $stripeSession */
            $stripeSession = $event->data->object;

            $reservation = DB::transaction(function () use (
                $stripeSession,
                $seatAvailabilityService,
            ) {
                $payment = Payments::query()
                    ->whereKey($stripeSession->metadata->payment_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($payment->status === 'paid') {
                    return null;
                }

                $reservationRequest = ReservationRequest::query()
                    ->whereKey($payment->reservation_request_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($reservationRequest->completed_at !== null) {
                    $payment->update([
                        'status' => 'paid',
                        'stripe_payment_intent_id' => $stripeSession->payment_intent,
                        'paid_at' => now(),
                    ]);

                    return null;
                }

                if ($reservationRequest->expires_at->isPast()) {
                    $payment->update([
                        'status' => 'expired',
                    ]);

                    return null;
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
                    $payment->update([
                        'status' => 'expired',
                    ]);

                    return null;
                }

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
                            $i + 1,
                        ),
                    ]);
                }

                $reservationRequest->update([
                    'completed_at' => now(),
                ]);

                $payment->update([
                    'status' => 'paid',
                    'stripe_payment_intent_id' => $stripeSession->payment_intent,
                    'paid_at' => now(),
                ]);

                return $reservation;
            });

            if ($reservation === null) {
                return response()->json(['received' => true]);
            }

            $ticketDownloadUrl = URL::temporarySignedRoute(
                'tickets.show',
                now()->addDays(7),
                ['reservation' => $reservation->id],
            );

            Mail::to($reservation->email)
                ->send(new ReservationConfirmationMail(
                    $reservation->load('tickets'),
                    $ticketDownloadUrl,
                ));
        }

        return response()->json(['received' => true]);
    }
}
