<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;

class PayPalGateway
{
    /**
     * Build the PayPal SDK client.
     */
    public function client(): PaypalServerSdkClient
    {
        return PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    (string) config('services.paypal.client_id'),
                    (string) config('services.paypal.client_secret')
                )
            )
            ->environment($this->environment())
            ->build();
    }

    /**
     * Verify a webhook signature with PayPal.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhookSignature(array $payload): Response
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->accessToken())
            ->acceptJson()
            ->post('/v1/notifications/verify-webhook-signature', $payload);
    }

    /**
     * Resolve the API environment string.
     */
    private function environment(): string
    {
        return config('services.paypal.environment') === 'live'
            ? Environment::PRODUCTION
            : Environment::SANDBOX;
    }

    /**
     * Resolve the PayPal REST base URL.
     */
    private function baseUrl(): string
    {
        return config('services.paypal.environment') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Fetch an OAuth access token from PayPal.
     */
    private function accessToken(): string
    {
        /** @var string $token */
        $token = Http::baseUrl($this->baseUrl())
            ->asForm()
            ->withBasicAuth(
                (string) config('services.paypal.client_id'),
                (string) config('services.paypal.client_secret')
            )
            ->post('/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ])
            ->throw()
            ->json('access_token');

        return $token;
    }
}
