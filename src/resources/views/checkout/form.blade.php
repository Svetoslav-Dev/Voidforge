@extends('layouts.app', ['title' => 'Checkout | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Checkout</p>
            <h1>Confirm shipping and place the order.</h1>
            <p class="lead">
                This step prepares your payment. The order is only placed after Stripe or PayPal confirms it.
            </p>
        </div>
    </section>

    @error('cart')
        <div class="errors">
            <strong>Checkout could not be completed.</strong>
            <p>{{ $message }}</p>
        </div>
    @enderror

    @if ($shippingAddresses->isNotEmpty())
        <section class="card" style="margin-top: 1.5rem;">
            <div class="actions" style="justify-content: space-between; align-items: end;">
                <form class="inline-form" method="POST" action="{{ route('checkout.address') }}">
                    @csrf
                    <label for="address">Saved shipping address</label>
                    <select id="address" name="address">
                        @foreach ($shippingAddresses as $address)
                            <option
                                value="{{ $address->id }}"
                                @selected($selectedShippingAddress?->id === $address->id)
                            >
                                {{ $address->label }}{{ $address->is_default ? ' · Default' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button class="button secondary" type="submit">Use address</button>
                </form>

                <a class="button secondary" href="{{ route('account.addresses.index') }}">Add new address</a>
            </div>
        </section>
    @endif

    <section class="checkout-layout">
        <form class="card checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf
            @if ($usesSavedAddresses)
                <input name="customer_name" type="hidden" value="{{ old('customer_name', $checkoutDefaults['customer_name']) }}">
                <input name="customer_email" type="hidden" value="{{ old('customer_email', $checkoutDefaults['customer_email']) }}">
                <input name="customer_phone" type="hidden" value="{{ old('customer_phone', $checkoutDefaults['customer_phone']) }}">
                <input name="shipping_address_line_1" type="hidden" value="{{ old('shipping_address_line_1', $checkoutDefaults['shipping_address_line_1']) }}">
                <input name="shipping_address_line_2" type="hidden" value="{{ old('shipping_address_line_2', $checkoutDefaults['shipping_address_line_2']) }}">
                <input name="shipping_city" type="hidden" value="{{ old('shipping_city', $checkoutDefaults['shipping_city']) }}">
                <input name="shipping_state" type="hidden" value="{{ old('shipping_state', $checkoutDefaults['shipping_state']) }}">
                <input name="shipping_postal_code" type="hidden" value="{{ old('shipping_postal_code', $checkoutDefaults['shipping_postal_code']) }}">
                <input name="shipping_country" type="hidden" value="{{ old('shipping_country', $checkoutDefaults['shipping_country']) }}">

                <div class="card" style="padding: 1rem; margin-bottom: 1rem;">
                    <p class="muted" style="margin-bottom: 0.35rem;">Selected shipping address</p>
                    <p style="margin: 0 0 0.35rem; font-weight: 700;">{{ old('customer_name', $checkoutDefaults['customer_name']) }}</p>
                    <p class="muted" style="margin: 0;">{{ old('customer_email', $checkoutDefaults['customer_email']) }}</p>
                    @if (filled(old('customer_phone', $checkoutDefaults['customer_phone'])))
                        <p class="muted" style="margin: 0;">{{ old('customer_phone', $checkoutDefaults['customer_phone']) }}</p>
                    @endif
                    <p class="muted" style="margin: 0.45rem 0 0;">
                        {{ old('shipping_address_line_1', $checkoutDefaults['shipping_address_line_1']) }}
                        @if (filled(old('shipping_address_line_2', $checkoutDefaults['shipping_address_line_2'])))
                            , {{ old('shipping_address_line_2', $checkoutDefaults['shipping_address_line_2']) }}
                        @endif
                    </p>
                    <p class="muted" style="margin: 0;">
                        {{ old('shipping_city', $checkoutDefaults['shipping_city']) }}
                        @if (filled(old('shipping_state', $checkoutDefaults['shipping_state'])))
                            , {{ old('shipping_state', $checkoutDefaults['shipping_state']) }}
                        @endif
                        , {{ old('shipping_postal_code', $checkoutDefaults['shipping_postal_code']) }}
                        , {{ $europeanCountries[old('shipping_country', $checkoutDefaults['shipping_country'])] ?? old('shipping_country', $checkoutDefaults['shipping_country']) }}
                    </p>
                </div>
            @else
                <div class="grid two">
                    <div class="field">
                        <label for="customer_name">Full name</label>
                        <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $checkoutDefaults['customer_name']) }}" required>
                        @error('customer_name')<p class="muted">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="customer_email">Email</label>
                        <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', $checkoutDefaults['customer_email']) }}" required>
                        @error('customer_email')<p class="muted">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid two">
                    <div class="field">
                        <label for="customer_phone">Phone</label>
                        <input id="customer_phone" name="customer_phone" type="text" value="{{ old('customer_phone', $checkoutDefaults['customer_phone']) }}">
                        @error('customer_phone')<p class="muted">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="shipping_country">Country code</label>
                        <select id="shipping_country" name="shipping_country" required>
                            @foreach ($europeanCountries as $countryCode => $countryName)
                                <option
                                    value="{{ $countryCode }}"
                                    @selected(old('shipping_country', $checkoutDefaults['shipping_country']) === $countryCode)
                                >
                                    {{ $countryName }} ({{ $countryCode }})
                                </option>
                            @endforeach
                        </select>
                        @error('shipping_country')<p class="muted">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="field">
                    <label for="shipping_address_line_1">Address line 1</label>
                    <input id="shipping_address_line_1" name="shipping_address_line_1" type="text" value="{{ old('shipping_address_line_1', $checkoutDefaults['shipping_address_line_1']) }}" required>
                    @error('shipping_address_line_1')<p class="muted">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="shipping_address_line_2">Address line 2</label>
                    <input id="shipping_address_line_2" name="shipping_address_line_2" type="text" value="{{ old('shipping_address_line_2', $checkoutDefaults['shipping_address_line_2']) }}">
                    @error('shipping_address_line_2')<p class="muted">{{ $message }}</p>@enderror
                </div>

                <div class="grid three">
                    <div class="field">
                        <label for="shipping_city">City</label>
                        <input id="shipping_city" name="shipping_city" type="text" value="{{ old('shipping_city', $checkoutDefaults['shipping_city']) }}" required>
                        @error('shipping_city')<p class="muted">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="shipping_state">State / Region</label>
                        <input id="shipping_state" name="shipping_state" type="text" value="{{ old('shipping_state', $checkoutDefaults['shipping_state']) }}">
                        @error('shipping_state')<p class="muted">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="shipping_postal_code">Postal code</label>
                        <input id="shipping_postal_code" name="shipping_postal_code" type="text" value="{{ old('shipping_postal_code', $checkoutDefaults['shipping_postal_code']) }}" required>
                        @error('shipping_postal_code')<p class="muted">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endif

            <div class="actions">
                <a class="button secondary" href="{{ route('cart.index') }}">Back to cart</a>
                <button type="submit">Place order</button>
            </div>
        </form>

        <aside class="card summary-card">
            <p class="muted">Order Summary</p>
            @foreach ($items as $item)
                <p class="summary-line plain-line">
                    <span>{{ $item['product']->name }} · {{ $item['size'] }} x {{ $item['quantity'] }}</span>
                    <strong>{{ number_format($item['line_total_cents'] / 100, 2) }} EUR</strong>
                </p>
            @endforeach
            <p class="summary-line plain-line">
                <span>Shipping</span>
                <strong>0.00 EUR</strong>
            </p>
            <p class="summary-line total-line">
                <span>Total</span>
                <strong>{{ number_format($subtotalCents / 100, 2) }} EUR</strong>
            </p>
            <p class="muted">Payment is not collected yet. Stripe and PayPal are the next steps.</p>
        </aside>
    </section>
@endsection
