<?php

namespace App\Services;

use App\Models\Payments;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\StripeClient;

class StripeCheckoutService
{
    public function createSession(Payments $payment, string $productName): StripeCheckoutSession
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        /** @var StripeCheckoutSession $checkoutSession */
        $checkoutSession = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => route('stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel'),
            'metadata' => [
                'payment_id' => (string) $payment->id,
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => $payment->currency,
                    'unit_amount' => $payment->amount,
                    'product_data' => [
                        'name' => $productName,
                    ],
                ],
                'quantity' => 1,
            ]],
        ]);

        return $checkoutSession;
    }

    public function retrieveSession(string $checkoutSessionId): StripeCheckoutSession
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        /** @var StripeCheckoutSession $checkoutSession */
        $checkoutSession = $stripe->checkout->sessions->retrieve($checkoutSessionId, []);

        return $checkoutSession;
    }
}
