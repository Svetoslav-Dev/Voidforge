<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
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
     * Create or update a Stripe customer.
     *
     * @param  array<string, mixed>  $payload
     * @return array{id: string}
     *
     * @throws ApiErrorException
     */
    public function createCustomer(array $payload): array
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $customer = Customer::create($payload);

        return [
            'id' => $customer->id,
        ];
    }

    /**
     * Retrieve a setup intent and its payment method details.
     *
     * @return array{id: string, payment_method_id: string, brand: ?string, last4: ?string, exp_month: ?int, exp_year: ?int}
     *
     * @throws ApiErrorException
     */
    public function retrieveSetupIntent(string $setupIntentId): array
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $setupIntent = SetupIntent::retrieve($setupIntentId);
        $paymentMethod = PaymentMethod::retrieve((string) $setupIntent->payment_method);

        return [
            'id' => $setupIntent->id,
            'payment_method_id' => $paymentMethod->id,
            'brand' => $paymentMethod->card?->brand,
            'last4' => $paymentMethod->card?->last4,
            'exp_month' => $paymentMethod->card?->exp_month,
            'exp_year' => $paymentMethod->card?->exp_year,
        ];
    }

    /**
     * Retrieve a Checkout session.
     *
     * @return array{id: string, mode: ?string, customer: ?string, setup_intent: ?string, payment_status: ?string, amount_total: ?int, metadata: array<string, mixed>}
     *
     * @throws ApiErrorException
     */
    public function retrieveCheckoutSession(string $sessionId): array
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $session = Session::retrieve($sessionId);

        return [
            'id' => $session->id,
            'mode' => $session->mode,
            'customer' => $session->customer ? (string) $session->customer : null,
            'setup_intent' => $session->setup_intent ? (string) $session->setup_intent : null,
            'payment_status' => $session->payment_status,
            'amount_total' => $session->amount_total !== null ? (int) $session->amount_total : null,
            'metadata' => $session->metadata?->toArray() ?? [],
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
