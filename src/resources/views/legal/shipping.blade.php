@extends('layouts.app', ['title' => 'Shipping And Delivery | VoidForgeStore'])

@section('content')
    <section class="card hero legal-page">
        <div>
            <h1>Shipping and Delivery</h1>
            <p class="lead">This page explains where VoidForgeStore ships, what delivery expectations apply, and how customers should handle delivery problems.</p>
        </div>
    </section>

    <section class="card legal-card">
        <h2>Delivery region</h2>
        <p>Current placeholder shipping region: {{ config('legal.shipping_regions') }}.</p>

        <h2>Dispatch timing</h2>
        <p>Current placeholder dispatch window: {{ config('legal.dispatch_window') }}.</p>

        <h2>Delivery timing</h2>
        <p>Final delivery windows should be published here once the actual carrier and supported countries are fixed.</p>

        <h2>Delivery issues</h2>
        <p>If delivery is delayed, fails, or the parcel arrives damaged, customers should contact {{ config('legal.support_email') }} and include the order reference where possible. Address mistakes supplied by the customer may still affect delivery timing or cost.</p>

        <h2>Current storefront note</h2>
        <p>The checkout summary currently shows shipping as a separate order component. Replace this page with the final carrier, delivery, and pricing wording before launch.</p>
    </section>
@endsection
