@extends('layouts.app', ['title' => 'Returns And Refunds | VoidForgeStore'])

@section('content')
    <section class="card hero legal-page">
        <div>
            <h1>Returns and Refunds</h1>
            <p class="lead">This page should explain how customers can withdraw from a purchase, return products, and receive refunds where applicable.</p>
        </div>
    </section>

    <section class="card legal-card">
        <h2>Return requests</h2>
        <p>Customers should use the contact details or request form on the Contact and Trader Information page to request return instructions from {{ config('legal.trader_name') }}.</p>

        <h2>Refund handling</h2>
        <p>Refunds should be returned through the payment method used for the original order, unless another lawful method is agreed with the customer. The current placeholder refund window is {{ config('legal.refund_window') }}.</p>

        <h2>Withdrawal rights</h2>
        <p>If consumer withdrawal rights apply to your sales, this page should clearly state the time window, any exceptions, and the exact process customers need to follow. The current placeholder window is {{ config('legal.returns_window_days') }} days.</p>

        <h2>Condition of returned goods</h2>
        <p>Returned goods should be sent back in a condition that allows the store to inspect them. If your final policy has product-specific exceptions, add them here before launch.</p>

        <h2>Store note</h2>
        <p>This page now reflects the real request path available on the storefront, but the final legal wording should still be reviewed for your products, geography, and trader obligations before launch.</p>
    </section>
@endsection
