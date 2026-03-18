@extends('layouts.app', ['title' => 'Order #'.$order->id.' | Admin'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Admin Order</p>
            <h1>Order #{{ $order->id }}</h1>
            <p class="lead">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
        </div>
    </section>

    <section class="grid two">
        <article class="card">
            <h2>Customer</h2>
            <p>{{ $order->customer_name }}</p>
            <p class="muted">{{ $order->customer_email }}</p>
            @if ($order->customer_phone)
                <p class="muted">{{ $order->customer_phone }}</p>
            @endif
        </article>

        <article class="card">
            <h2>Totals</h2>
            <p class="muted">Subtotal: {{ number_format($order->subtotal_cents / 100, 2) }} EUR</p>
            <p class="muted">Shipping: {{ number_format($order->shipping_cents / 100, 2) }} EUR</p>
            <p><strong>Total: {{ number_format($order->total_cents / 100, 2) }} EUR</strong></p>
        </article>
    </section>

    <section class="grid two">
        <article class="card">
            <h2>Shirts</h2>
            @foreach ($order->items as $item)
                <p class="summary-line plain-line">
                    <span>{{ $item->product_name }} · {{ $item->product_size }} x {{ $item->quantity }}</span>
                    <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                </p>
            @endforeach
        </article>

        <article class="card">
            <h2>Payments</h2>
            @forelse ($order->payments as $payment)
                <p class="summary-line plain-line">
                    <span>{{ ucfirst($payment->provider) }} · {{ $payment->status }}</span>
                    <strong>{{ number_format($payment->amount / 100, 2) }} EUR</strong>
                </p>
            @empty
                <p class="muted">No payment records yet.</p>
            @endforelse
        </article>
    </section>
@endsection
