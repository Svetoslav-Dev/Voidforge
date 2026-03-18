<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeGateway
{
    /**
     * Create a Stripe Checkout session.
     *
     * @param  array<string, mixed>  $payload
     * @return array{id: string, url: string}
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(array $payload): array
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $session = Session::create($payload);

        return [
            'id' => $session->id,
            'url' => (string) $session->url,
        ];
    }

    /**
     * Validate and construct a Stripe webhook event.
     *
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, ?string $signature): Event
    {
        return Webhook::constructEvent(
            $payload,
            (string) $signature,
            (string) config('services.stripe.webhook_secret')
        );
    }
}
