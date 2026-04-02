@extends('layouts.app', ['title' => __('cart.page_title')])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">{{ __('cart.heading') }}</h1>
            <p class="lead">{{ __('cart.lead') }}</p>
        </div>
    </section>

    @if ($items->isEmpty())
        <section class="card empty-state cart-empty-state">
            <h2>{{ __('cart.empty_heading') }}</h2>
            <p class="muted">{{ __('cart.empty_hint') }}</p>
        </section>
    @else
        <section class="cart-layout">
            <div class="cart-items">
                @foreach ($items as $item)
                    <article class="card cart-item">
                        <div class="cart-item-main">
                            <div class="product-visual cart-item-visual">
                                <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}">
                            </div>

                            <div>
                                <p class="muted">{{ $item['product']->category?->name ?? __('products.uncategorized') }}</p>
                                <h2>{{ $item['product']->name }}</h2>
                                <p class="muted">{{ __('cart.sku', ['sku' => $item['product']->sku]) }}</p>
                                <p class="muted">{{ __('cart.size', ['size' => $item['size']]) }}</p>
                                <p class="cart-item-price">{{ __('cart.price_each', ['price' => number_format($item['product']->price_cents / 100, 2)]) }}</p>
                            </div>
                        </div>

                        <div class="cart-item-actions">
                            <form class="inline-form" method="POST" action="{{ route('cart.update', $item['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="size" value="{{ $item['size'] }}">
                                <label for="quantity-{{ $item['key'] }}">{{ __('cart.qty') }}</label>
                                <input
                                    id="quantity-{{ $item['key'] }}"
                                    name="quantity"
                                    type="number"
                                    min="0"
                                    max="{{ min(99, $item['product']->stock) }}"
                                    value="{{ $item['quantity'] }}"
                                >
                                <button type="submit">{{ __('cart.update') }}</button>
                            </form>

                            <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="size" value="{{ $item['size'] }}">
                                <button class="button secondary" type="submit">{{ __('cart.remove') }}</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="card summary-card cart-summary-card">
                <p class="cart-order-summary-title">{{ __('cart.order_summary') }}</p>
                <form method="POST" action="{{ route('cart.discount.store') }}">
                    @csrf
                    <div class="field" style="margin-bottom: 0.5rem;">
                        <label for="discount_code">{{ __('cart.discount_code') }}</label>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input id="discount_code" name="code" type="text" value="{{ old('code', $discountSummary['code'] ?? '') }}" placeholder="{{ __('cart.discount_placeholder') }}">
                            <button type="submit">{{ __('cart.apply') }}</button>
                        </div>
                        @error('code')<p class="muted">{{ $message }}</p>@enderror
                    </div>
                </form>

                @if ($discountSummary)
                    <div class="actions" style="margin-top: 0;">
                        <p class="discount-applied-label" style="margin:0;">{{ __('cart.discount_applied', ['code' => $discountSummary['code']]) }}</p>
                        <form method="POST" action="{{ route('cart.discount.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">{{ __('cart.remove') }}</button>
                        </form>
                    </div>
                @endif

                @php($summaryItems = $items->groupBy(fn ($item) => $item['product']->id)->map(function ($group) {
                    return [
                        'name' => $group->first()['product']->name,
                        'quantity' => $group->sum('quantity'),
                        'line_total_cents' => $group->sum('line_total_cents'),
                    ];
                }))
                @foreach ($summaryItems as $item)
                    <p class="summary-line plain-line">
                        <span>{{ $item['name'] }} x {{ $item['quantity'] }}</span>
                        <strong>{{ number_format($item['line_total_cents'] / 100, 2) }} EUR</strong>
                    </p>
                @endforeach
                @if ($discountSummary)
                    <p class="summary-line plain-line discount-summary-line">
                        <span>{{ __('cart.discount_line', ['code' => $discountSummary['code']]) }}</span>
                        <strong>-{{ number_format($discountSummary['discount_cents'] / 100, 2) }} EUR</strong>
                    </p>
                @endif
                <p class="summary-line">
                    <span>{{ __('cart.subtotal') }}</span>
                    <strong>{{ number_format($subtotalCents / 100, 2) }} EUR</strong>
                </p>
                <p class="summary-line total-line">
                    <span>{{ __('cart.total') }}</span>
                    <strong>{{ number_format(($discountSummary['total_cents'] ?? $subtotalCents) / 100, 2) }} EUR</strong>
                </p>
                <p class="muted">{{ __('cart.shipping_note') }}</p>

                <div class="actions cart-summary-actions">
                    <a class="button cart-checkout-button" href="{{ route('checkout.index') }}">{{ __('cart.continue_to_shipping') }}</a>
                </div>
            </aside>
        </section>
    @endif
@endsection
