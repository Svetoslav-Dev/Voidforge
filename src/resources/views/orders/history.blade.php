@extends('layouts.app', ['title' => __('orders.history_page_title')])

@section('content')
    <section class="card hero">
        <div>
            <h1>{{ __('orders.history_heading') }}</h1>
            <p class="lead">{{ __('orders.history_lead') }}</p>
        </div>
    </section>

    <div data-order-history>
        <div class="chip-row" style="margin-top: 1.5rem;" data-order-history-chips>
            <a class="chip {{ $activeStatus === null ? 'active' : '' }}" href="{{ route('orders.index') }}">{{ __('orders.filter_all') }}</a>
            <a class="chip {{ $activeStatus === 'paid' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'paid']) }}">{{ __('orders.filter_paid') }}</a>
            <a class="chip {{ $activeStatus === 'awaiting_payment' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'awaiting_payment']) }}">{{ __('orders.filter_awaiting') }}</a>
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
                            · {{ __('orders.shirts_count', ['count' => $order->items->sum('quantity')]) }}
                        </p>
                    </div>

                    <div style="text-align: right;">
                        <strong>{{ number_format($order->total_cents / 100, 2) }} EUR</strong>
                        <p class="muted">
                            {{ $order->payments->first()?->provider ? ucfirst($order->payments->first()->provider) : __('orders.awaiting_payment') }}
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
                    <a class="button secondary" href="{{ route('orders.show', ['orderReference' => 'VF'.$order->id]) }}">{{ __('orders.open_receipt') }}</a>
                    @if ($order->placed_at)
                        <a class="button secondary" href="{{ route('orders.download', ['orderReference' => 'VF'.$order->id]) }}">{{ __('orders.download_pdf') }}</a>
                    @endif
                </div>
            </article>
        @empty
            <article class="card empty-state">
                <h2>{{ __('orders.empty_heading') }}</h2>
                <p class="muted">{{ __('orders.empty_hint') }}</p>
                <div class="actions">
                    <a class="button secondary" href="{{ route('products.index') }}">{{ __('orders.browse_shirts') }}</a>
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
