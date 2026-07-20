<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmationMail;
use App\Models\Payments;
use App\Models\Reservation;
use App\Services\SeatAvailabilityService;
use App\Services\StripeCheckoutFinalizer;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class StripeController extends Controller
{
    public function success(
        Request $request,
        StripeCheckoutService $stripeCheckoutService,
        SeatAvailabilityService $seatAvailabilityService,
        StripeCheckoutFinalizer $finalizer,
    ): Response {
        $sessionId = trim((string) $request->string('session_id'));

        if ($sessionId !== '') {
            $stripeSession = $stripeCheckoutService->retrieveSession($sessionId);
            $stripeCheckoutSessionId = (string) data_get($stripeSession, 'id', '');
            $stripePaymentIntentId = (string) data_get($stripeSession, 'payment_intent', '');
            $paymentId = (string) data_get($stripeSession, 'metadata.payment_id', '');

            if (
                $stripeCheckoutSessionId === ''
                || $stripePaymentIntentId === ''
                || $paymentId === ''
            ) {
                return Inertia::render('reservation/Confirmed');
            }

            $reservation = $finalizer->finalizeCompletedSession(
                $stripeCheckoutSessionId,
                $stripePaymentIntentId,
                $paymentId,
                $seatAvailabilityService,
            );

            if ($reservation instanceof Reservation) {
                $ticketDownloadUrl = URL::temporarySignedRoute(
                    'tickets.show',
                    now()->addDays(7),
                    ['reservation' => $reservation->id],
                );

                Mail::to($reservation->email)->send(
                    new ReservationConfirmationMail(
                        $reservation->load('cinemaSession.movie', 'cinemaSession.room', 'tickets'),
                        $ticketDownloadUrl,
                    ),
                );
            }
        }

        return Inertia::render('reservation/Confirmed');
    }

    public function cancel(): Response
    {
        return Inertia::render('reservation/Cancel');
    }

    public function handle(
        Request $request,
        SeatAvailabilityService $seatAvailabilityService,
        StripeCheckoutFinalizer $finalizer,
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
            $stripeCheckoutSessionId = (string) data_get($stripeSession, 'id', '');
            $stripePaymentIntentId = (string) data_get($stripeSession, 'payment_intent', '');
            $paymentId = (string) data_get($stripeSession, 'metadata.payment_id', '');

            if (
                $stripeCheckoutSessionId === ''
                || $stripePaymentIntentId === ''
                || $paymentId === ''
            ) {
                return response()->json(['received' => true]);
            }

            $reservation = $finalizer->finalizeCompletedSession(
                $stripeCheckoutSessionId,
                $stripePaymentIntentId,
                $paymentId,
                $seatAvailabilityService,
            );

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
                    $reservation->load('cinemaSession.movie', 'cinemaSession.room', 'tickets'),
                    $ticketDownloadUrl,
                ));
        }

        return response()->json(['received' => true]);
    }
}
