@extends('layouts.app', ['title' => 'Checkout | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">Confirm shipping</h1>
            <p class="lead">
                This step prepares your shipping address.
            </p>
        </div>
    </section>

    @error('cart')
        <div class="errors">
            <strong>Checkout could not be completed.</strong>
            <p>{{ $message }}</p>
        </div>
    @enderror

    <div data-shipping-page>
        @if ($shippingAddresses->isNotEmpty())
            <section class="card" style="margin-top: 1.5rem;">
                <form method="POST" action="{{ route('checkout.address') }}"
                    class="flex items-center w-full gap-4"
                    data-checkout-address-form>
                    @csrf

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; width:100%;">
                        <div class="field" style="margin:0; display:flex; align-items:center; gap:0.75rem;">
                            <label for="address" style="margin:0; white-space:nowrap;">Saved shipping address</label>

                            <select id="address" name="address" style="width:18rem;">
                                @foreach ($shippingAddresses as $address)
                                    <option
                                        value="{{ $address->id }}"
                                        @selected($selectedShippingAddress?->id === $address->id)
                                    >
                                        {{ $address->label }}{{ $address->is_default ? ' · Default' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display:flex; align-items:center; gap:0.5rem; white-space:nowrap;">
                            <button class="button secondary" type="submit" style="display:inline-flex; width:auto;">
                                Use address
                            </button>
                            <a class="button secondary" href="{{ route('account.addresses.index') }}" style="display:inline-flex; width:auto;">
                                Add new address
                            </a>
                        </div>
                    </div>
                </form>
            </section>
        @endif

        <section class="checkout-layout">
            <form class="card checkout-form {{ $usesSavedAddresses ? '' : 'checkout-form--surface' }}" method="POST" action="{{ route('checkout.store') }}">
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

                <div class="card checkout-selected-address-card">
                    <p class="checkout-selected-address-title">Selected shipping address:</p>
                    <p><strong>Name:</strong> <span class="muted">{{ old('customer_name', $checkoutDefaults['customer_name']) }}</span></p>
                    <p><strong>Email:</strong> <span class="muted">{{ old('customer_email', $checkoutDefaults['customer_email']) }}</span></p>
                    @if (filled(old('customer_phone', $checkoutDefaults['customer_phone'])))
                        <p><strong>Phone:</strong> <span class="muted">{{ old('customer_phone', $checkoutDefaults['customer_phone']) }}</span></p>
                    @endif
                    <p>
                        <strong>Address:</strong>
                        <span class="muted">
                            {{ old('shipping_address_line_1', $checkoutDefaults['shipping_address_line_1']) }}
                            @if (filled(old('shipping_address_line_2', $checkoutDefaults['shipping_address_line_2'])))
                                , {{ old('shipping_address_line_2', $checkoutDefaults['shipping_address_line_2']) }}
                            @endif
                        </span>
                    </p>
                    <p><strong>City:</strong> <span class="muted">{{ old('shipping_city', $checkoutDefaults['shipping_city']) }}</span></p>
                    @if (filled(old('shipping_state', $checkoutDefaults['shipping_state'])))
                        <p><strong>State:</strong> <span class="muted">{{ old('shipping_state', $checkoutDefaults['shipping_state']) }}</span></p>
                    @endif
                    <p><strong>Postal code:</strong> <span class="muted">{{ old('shipping_postal_code', $checkoutDefaults['shipping_postal_code']) }}</span></p>
                    <p><strong>Country:</strong> <span class="muted">{{ $europeanCountries[old('shipping_country', $checkoutDefaults['shipping_country'])] ?? old('shipping_country', $checkoutDefaults['shipping_country']) }}</span></p>
                </div>
            @else
                <div class="grid two checkout-guest-grid">
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

                <div class="grid two checkout-guest-grid">
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

            <div class="actions checkout-form-actions">
                <a class="button secondary" href="{{ route('cart.index') }}">Back to cart</a>
                <button type="submit">Place order</button>
            </div>
            </form>

            <aside class="card summary-card checkout-shipping-summary-card">
                <p class="checkout-order-summary-title">Order summary:</p>
                <form method="POST" action="{{ route('cart.discount.store') }}">
                    @csrf
                    <div class="field" style="margin-bottom: 0.5rem;">
                        <label for="shipping_discount_code">Discount code</label>
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input id="shipping_discount_code" name="code" type="text" value="{{ old('code', $discountSummary['code'] ?? '') }}" placeholder="WELCOME10">
                            <button type="submit">Apply</button>
                        </div>
                        @error('code')<p class="muted">{{ $message }}</p>@enderror
                    </div>
                </form>
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
                @if ($discountSummary)
                    <div class="actions" style="margin-top: 0;">
                        <p class="discount-applied-label" style="margin:0;">Applied code {{ $discountSummary['code'] }}</p>
                        <form method="POST" action="{{ route('cart.discount.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Remove</button>
                        </form>
                    </div>
                    <p class="summary-line plain-line discount-summary-line">
                        <span>Discount · {{ $discountSummary['code'] }}</span>
                        <strong>-{{ number_format($discountSummary['discount_cents'] / 100, 2) }} EUR</strong>
                    </p>
                @endif
                <p class="summary-line plain-line">
                    <span>Subtotal</span>
                    <strong>{{ number_format($subtotalCents / 100, 2) }} EUR</strong>
                </p>
                <p class="summary-line total-line">
                    <span>Total</span>
                    <strong>{{ number_format(($discountSummary['total_cents'] ?? $subtotalCents) / 100, 2) }} EUR</strong>
                </p>
                <p class="muted">Payment is not collected yet. Stripe and PayPal are the next steps.</p>

            </aside>
        </section>
    </div>
@endsection
