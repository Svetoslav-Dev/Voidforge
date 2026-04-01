@extends('layouts.app', ['title' => 'Purchase History | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Your paid and pending shirt receipts.</h1>
            <p class="lead">
                Review previous checkouts, reopen receipts, and see which payment provider was used for each order.
            </p>
        </div>
    </section>

    <div data-order-history>
        <div class="chip-row" style="margin-top: 1.5rem;" data-order-history-chips>
            <a class="chip {{ $activeStatus === null ? 'active' : '' }}" href="{{ route('orders.index') }}">All</a>
            <a class="chip {{ $activeStatus === 'paid' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'paid']) }}">Paid</a>
            <a class="chip {{ $activeStatus === 'awaiting_payment' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'awaiting_payment']) }}">Awaiting payment</a>
        </div>

    <section class="list-stack" style="margin-top: 1rem;">
        @forelse ($orders as $order)
            <article class="card">
                <div class="receipt-header">
                    <div>
                        <p class="muted">Order #VF{{ $order->id }}</p>
                        <h2 style="margin: 0 0 0.4rem; color: #4ecba3;">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</h2>
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

                <div class="actions">
                    <a class="button secondary" href="{{ route('orders.show', ['orderReference' => 'VF'.$order->id]) }}">Open receipt</a>
                    @if ($order->placed_at)
                        <a class="button secondary" href="{{ route('orders.download', ['orderReference' => 'VF'.$order->id]) }}">Download PDF</a>
                    @endif
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

    @if ($orders->hasPages())
        <div class="pagination-wrap">
            {{ $orders->links() }}
        </div>
    @endif
    </div>
@endsection
