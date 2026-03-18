@extends('layouts.app', ['title' => 'Purchase History | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Purchase History</p>
            <h1>Your paid and pending shirt receipts.</h1>
            <p class="lead">
                Review previous checkouts, reopen receipts, and see which payment provider was used for each order.
            </p>
        </div>
    </section>

    <section class="list-stack" style="margin-top: 1.5rem;">
        @forelse ($orders as $order)
            <article class="card">
                <div class="receipt-header">
                    <div>
                        <p class="muted">Order #{{ $order->id }}</p>
                        <h2 style="margin: 0 0 0.4rem;">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</h2>
                        <p class="muted">
                            {{ optional($order->placed_at)->format('M d, Y') ?? $order->created_at->format('M d, Y') }}
                            · {{ $order->items->sum('quantity') }} shirts
                        </p>
                    </div>

                    <div style="text-align: right;">
                        <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
                        <p class="muted">
                            {{ $order->payments->first()?->provider ? ucfirst($order->payments->first()->provider) : 'Awaiting payment' }}
                        </p>
                    </div>
                </div>

                @foreach ($order->items as $item)
                    <p class="summary-line plain-line">
                        <span>{{ $item->product_name }} · {{ $item->product_size }} x {{ $item->quantity }}</span>
                        <strong>{{ number_format($item->line_total_cents / 100, 2) }} EUR</strong>
                    </p>
                @endforeach

                <div class="actions">
                    <a class="button secondary" href="{{ route('orders.show', $order) }}">Open receipt</a>
                </div>
            </article>
        @empty
            <article class="card empty-state">
                <h2>No purchases yet</h2>
                <p class="muted">Once you complete checkout, your receipts will be listed here.</p>
                <div class="actions">
                    <a class="button secondary" href="{{ route('products.index') }}">Browse shirts</a>
                </div>
            </article>
        @endforelse
    </section>
@endsection
