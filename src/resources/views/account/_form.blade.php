@php
    $address = $shippingAddress ?? null;
@endphp

<form method="POST" action="{{ $formAction }}">
    @csrf
    @isset($formMethod)
        @method($formMethod)
    @endisset

    <div class="field">
        <label for="label">Label</label>
        <input id="label" name="label" type="text" value="{{ old('label', $address?->label) }}" placeholder="Home, Office, Studio" required>
    </div>

    <div class="field">
        <label for="recipient_name">Recipient name</label>
        <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', $address?->recipient_name ?? auth()->user()->name) }}" required>
    </div>

    <div class="field">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $address?->phone) }}">
    </div>

    <div class="field">
        <label for="address_line_1">Address line 1</label>
        <input id="address_line_1" name="address_line_1" type="text" value="{{ old('address_line_1', $address?->address_line_1) }}" required>
    </div>

    <div class="field">
        <label for="address_line_2">Address line 2</label>
        <input id="address_line_2" name="address_line_2" type="text" value="{{ old('address_line_2', $address?->address_line_2) }}">
    </div>

    <div class="grid two">
        <div class="field">
            <label for="city">City</label>
            <input id="city" name="city" type="text" value="{{ old('city', $address?->city) }}" required>
        </div>

        <div class="field">
            <label for="state">State / Region</label>
            <input id="state" name="state" type="text" value="{{ old('state', $address?->state) }}">
        </div>
    </div>

    <div class="grid two">
        <div class="field">
            <label for="postal_code">Postal code</label>
            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $address?->postal_code) }}" required>
        </div>

        <div class="field">
            <label for="country">Country</label>
            <select id="country" name="country" required>
                @foreach (\App\Http\Requests\Checkout\StoreCheckoutRequest::europeanCountries() as $countryCode => $countryName)
                    <option value="{{ $countryCode }}" @selected(old('country', $address?->country ?? 'BG') === $countryCode)>{{ $countryName }} ({{ $countryCode }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <label class="checkbox">
        <input type="checkbox" name="is_default" value="1" @checked((bool) old('is_default', $address?->is_default))>
        <span>Set as default shipping address</span>
    </label>

    <div class="actions">
        <button type="submit">{{ $submitLabel }}</button>
    </div>
</form>
