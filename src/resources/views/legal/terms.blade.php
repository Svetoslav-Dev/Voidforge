@extends('layouts.app', ['title' => 'Terms And Conditions | VoidForgeStore'])

@section('content')
    <section class="card hero legal-page">
        <div>
            <h1>Terms and Conditions</h1>
            <p class="lead">These terms describe how orders are placed through VoidForgeStore, what customers can expect before payment, and what rules govern the storefront.</p>
        </div>
    </section>

    <section class="card legal-card">
        <h2>Storefront scope</h2>
        <p>{{ config('legal.trader_name') }} offers apparel products for sale through the online catalog, cart, shipping, and checkout flow shown on the site.</p>

        <h2>Orders and acceptance</h2>
        <p>Adding products to the cart does not create a contract by itself. Orders are only finalized after the payment provider confirms successful payment.</p>

        <h2>Pricing and availability</h2>
        <p>Prices are shown in EUR. Product availability depends on stock at the time the order is placed and paid. Discount codes apply only when they are valid and accepted during cart or shipping checkout.</p>

        <h2>Payments</h2>
        <p>Payments are handled through Stripe and PayPal hosted flows. VoidForgeStore stores only the payment details needed for order records, such as provider, transaction reference, amount, and status.</p>

        <h2>Delivery and fulfilment</h2>
        <p>Shipping details are collected before the customer is redirected to the payment provider. Dispatch and delivery timing should be interpreted together with the Shipping and Delivery page published on the storefront.</p>

        <h2>Customer responsibilities</h2>
        <p>Customers are responsible for providing accurate shipping and contact information before confirming the order.</p>

        <h2>Legal review note</h2>
        <p>This page now reflects the current storefront flow, but it still needs final governing-law, trader-registration, VAT, and complaint-handling details before going live.</p>
    </section>
@endsection
