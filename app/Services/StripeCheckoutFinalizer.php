<?php

namespace App\Services;

use App\Models\CinemaSession;
use App\Models\Payments;
use App\Models\Reservation;
use App\Models\ReservationRequest;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class StripeCheckoutFinalizer
{
    public function finalizeCompletedSession(
        string $stripeCheckoutSessionId,
        string $stripePaymentIntentId,
        string $paymentId,
        SeatAvailabilityService $seatAvailabilityService,
    ): ?Reservation {
        return DB::transaction(function () use (
            $stripeCheckoutSessionId,
            $stripePaymentIntentId,
            $paymentId,
            $seatAvailabilityService,
        ): ?Reservation {
            $payment = Payments::query()
                ->whereKey($paymentId)
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
                    'stripe_checkout_session_id' => $stripeCheckoutSessionId,
                    'stripe_payment_intent_id' => $stripePaymentIntentId,
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
                'stripe_checkout_session_id' => $stripeCheckoutSessionId,
                'stripe_payment_intent_id' => $stripePaymentIntentId,
                'paid_at' => now(),
            ]);

            return $reservation;
        });
    }
}
