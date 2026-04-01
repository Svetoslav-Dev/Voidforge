@extends('layouts.app', ['title' => 'VoidForgeStore'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">Echoes of the Void</h1>
            <p class="lead">
                VoidForgeStore is a dark-themed shirt storefront built around a sharp, minimal aesthetic.
                Browse the catalog, pick your size, and check out securely — every order comes with a downloadable PDF receipt.
            </p>
        </div>

        <div class="grid two">
            <div class="card card-plain">
                <h2 style="color: #4ecba3;">What you can do</h2>
                <p class="muted">Browse shirts by category, add items to your cart, apply discount codes at checkout, enter a shipping address, and pay with Stripe or PayPal. A PDF invoice is available to download as soon as your order is confirmed.</p>
            </div>

            <div class="card card-plain">
                <h2 style="color: #4ecba3;">Your account</h2>
                <p class="muted">Create an account to save multiple shipping addresses, store a card on file with Stripe for faster checkout, and access your full order history with receipt downloads from your account page.</p>
            </div>

            <div class="card card-plain">
                <h2 style="color: #4ecba3;">Payments</h2>
                <p class="muted">Payments are processed through hosted Stripe and PayPal sessions — no card details are ever stored on this server. Google Pay and Apple Pay are supported and appear automatically at Stripe checkout when your device and browser allow it.</p>
            </div>

            <div class="card card-plain">
                <h2 style="color: #4ecba3;">Legal and support</h2>
                <p class="muted">The full legal pages — privacy policy, terms and conditions, returns, shipping, and cookie policy — are linked in the footer. To track an order or get support, use the order tracking page or reach out via the contact details below.</p>
            </div>
        </div>
    </section>

@endsection
