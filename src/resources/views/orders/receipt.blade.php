@extends('layouts.app', ['title' => 'Receipt #'.$order->id.' | Voidforge'])

@section('content')
    <section class="card hero">
        <div class="receipt-header">
            <div>
                <p class="muted">Receipt</p>
                <h1>Order #{{ $order->id }}</h1>
                <p class="lead">
                    Purchased by {{ $order->customer_name }} on
                    {{ optional($order->placed_at)->format('F d, Y') ?? $order->created_at->format('F d, Y') }}.
                </p>
            </div>

            <div style="text-align: right;">
                <p class="muted">Status</p>
                <h2 style="margin: 0;">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</h2>
            </div>
        </div>
    </section>

    <section class="grid two" style="margin-top: 1.5rem;">
        <article class="card">
            <h2>Shirts</h2>
            @foreach ($order->items as $item)
                <div class="receipt-item">
                    <div class="product-visual">
                        <img src="{{ $item->product?->image_url ?? asset('images/items/fallback.svg') }}" alt="{{ $item->product_name }}">
                    </div>

                    <p class="summary-line plain-line">
                        <span>{{ $item->product_name }} · {{ $item->product_size }} x {{ $item->quantity }}</span>
                        <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                    </p>
                </div>
            @endforeach

            <p class="summary-line total-line">
                <span>Total</span>
                <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
            </p>
        </article>

        <article class="card">
            <h2>Receipt Details</h2>
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

            @if ($order->payments->isNotEmpty())
                @foreach ($order->payments as $payment)
                    <p class="summary-line plain-line">
                        <span>{{ ucfirst($payment->provider) }} · {{ $payment->status }}</span>
                        <strong>{{ number_format($payment->amount / 100, 2) }} EUR</strong>
                    </p>
                    <p class="muted">Transaction: {{ $payment->transaction_id }}</p>
                @endforeach
            @else
                <p class="muted">No completed payment has been recorded for this order yet.</p>
            @endif

            <div class="actions">
                <a class="button secondary" href="{{ route('orders.index') }}">Back to history</a>
                <a class="button secondary" href="{{ route('products.index') }}">Browse shirts</a>
            </div>
        </article>
    </section>
@endsection
