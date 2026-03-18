@extends('layouts.app', ['title' => 'Order #'.$order->id.' | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Order Confirmation</p>
            <h1>Order #{{ $order->id }} is {{ str_replace('_', ' ', $order->status) }}.</h1>
            <p class="lead">
                The order has been created and stock has been reserved.
                @if ($order->status === 'paid')
                    A payment provider has confirmed the payment for this order.
                @else
                    Complete payment with Stripe Checkout or PayPal.
                @endif
            </p>
        </div>
    </section>

    @error('payment')
        <div class="errors">
            <strong>Payment could not be started.</strong>
            <p>{{ $message }}</p>
        </div>
    @enderror

    <section class="grid two">
        <article class="card">
            <h2>Shipment</h2>
            <p>{{ $order->customer_name }}</p>
            <p class="muted">{{ $order->customer_email }}</p>
            @if ($order->customer_phone)
                <p class="muted">{{ $order->customer_phone }}</p>
            @endif
            <p class="muted">
                {{ $order->shipping_address_line_1 }}
                @if ($order->shipping_address_line_2)
                    , {{ $order->shipping_address_line_2 }}
                @endif
                , {{ $order->shipping_city }}
                @if ($order->shipping_state)
                    , {{ $order->shipping_state }}
                @endif
                , {{ $order->shipping_postal_code }}
                , {{ $order->shipping_country }}
            </p>
        </article>

        <article class="card">
            <h2>Summary</h2>
            @foreach ($order->items as $item)
                <p class="summary-line plain-line">
                    <span>{{ $item->product_name }} · {{ $item->product_size }} x {{ $item->quantity }}</span>
                    <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                </p>
            @endforeach
            <p class="summary-line plain-line">
                <span>Status</span>
                <strong>{{ ucfirst($order->status) }}</strong>
            </p>
            <p class="summary-line total-line">
                <span>Total</span>
                <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
            </p>

            @if ($order->status !== 'paid')
                <div class="actions">
                    <form method="POST" action="{{ route('stripe.checkout.store', $order) }}">
                        @csrf
                        <button type="submit">Pay with Stripe</button>
                    </form>

                    <form method="POST" action="{{ route('paypal.checkout.store', $order) }}">
                        @csrf
                        <button class="button secondary" type="submit">Pay with PayPal</button>
                    </form>
                </div>
            @endif

            <div class="actions">
                @auth
                    <a class="button secondary" href="{{ route('orders.index') }}">Purchase history</a>
                @endauth
                <a class="button secondary" href="{{ route('products.index') }}">Browse shirts</a>
            </div>
        </article>
    </section>
@endsection
