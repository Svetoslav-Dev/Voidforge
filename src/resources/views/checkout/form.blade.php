@extends('layouts.app', ['title' => 'Checkout | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Checkout</p>
            <h1>Confirm shipping and place the order.</h1>
            <p class="lead">
                This step creates the order record and reserves stock. Payment providers come next, so the
                order stays in a pending state for now.
            </p>
        </div>
    </section>

    @error('cart')
        <div class="errors">
            <strong>Checkout could not be completed.</strong>
            <p>{{ $message }}</p>
        </div>
    @enderror

    <section class="checkout-layout">
        <form class="card checkout-form" method="POST" action="{{ route('checkout.store') }}">
            @csrf
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
