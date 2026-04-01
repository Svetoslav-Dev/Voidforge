@extends('layouts.app', ['title' => 'Privacy Policy | VoidForgeStore'])

@section('content')
    <section class="card hero legal-page">
        <div>
            <h1>Privacy Policy</h1>
            <p class="lead">This page explains what customer data VoidForgeStore uses to run the storefront, process orders, and support account features.</p>
        </div>
    </section>

    <section class="card legal-card">
        <h2>Who operates the site</h2>
        <p>{{ config('legal.trader_name') }} operates this storefront and acts as the contact point for customer and privacy-related questions.</p>
        <p>Trader address: {{ config('legal.trader_address') }}</p>
        <p>Privacy contact: {{ config('legal.privacy_email') }}</p>

        <h2>What data is used</h2>
        <p>VoidForgeStore may process account details, shipping details, order details, payment status records, saved addresses, saved payment method metadata, and support messages needed to operate the store.</p>

        <h2>Why the data is used</h2>
        <p>Customer data is used to create accounts, process orders, provide receipts, support checkout, send transactional emails, send cart reminders to signed-in users, and prevent abuse of the storefront.</p>

        <h2>Payments and third parties</h2>
        <p>Payments are processed through hosted Stripe and PayPal flows. VoidForgeStore does not store raw card data locally. Transactional emails and public support requests are sent through the configured mail provider.</p>

        <h2>Storage and retention</h2>
        <p>Order and account data may be retained for operational, accounting, security, fraud-prevention, and customer-support reasons. Saved addresses and saved payment method metadata remain linked to the customer account until they are changed or removed by the customer or store operator.</p>

        <h2>Retention and customer rights</h2>
        <p>Customers should be able to request access, correction, or deletion where applicable, subject to accounting, security, and other lawful retention duties that still apply to completed orders or account records.</p>
        <p>The exact retention wording should still be reviewed against your final Bulgarian and EU legal obligations before launch.</p>
        <p><a href="{{ route('order.tracking') }}">Track an order</a></p>
    </section>
@endsection
