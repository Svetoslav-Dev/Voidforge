@extends('layouts.app', ['title' => 'Contact And Trader Information | VoidForgeStore'])

@section('content')
    <section class="card hero legal-page">
        <div>
            <h1>Contact and Trader Information</h1>
            <p class="lead">Use this page to publish the storefront contact details and the final trader identity details customers should see before placing an order.</p>
        </div>
    </section>

    <section class="card legal-card">
        <h2>Customer contact</h2>
        <p>Email: {{ config('legal.support_email') }}</p>
        <p>Phone: {{ config('legal.support_phone') }}</p>
        <p>Address: {{ config('legal.trader_address') }}</p>

        <h2>Trader details</h2>
        <p>Trader name: {{ config('legal.trader_name') }}</p>
        @if (filled(config('legal.trader_registration_number')))
            <p>Registration number: {{ config('legal.trader_registration_number') }}</p>
        @else
            <p>Registration number: add the final registered business number before launch.</p>
        @endif
        @if (filled(config('legal.trader_vat_number')))
            <p>VAT number: {{ config('legal.trader_vat_number') }}</p>
        @else
            <p>VAT number: add the final VAT number here if applicable.</p>
        @endif
        <p>Complaints contact: {{ config('legal.complaints_email') }}</p>

        <h2>Privacy and support requests</h2>
        <p>Customers should use the published contact details for order support, privacy requests, return requests, and other storefront questions. Include the order reference where possible so the request can be reviewed faster.</p>
    </section>

    <section class="card legal-card">
        <h2>Open a request</h2>

        <form method="POST" action="{{ route('legal.contact.request') }}" class="legal-request-form">
            @csrf

            <div class="grid two">
                <div class="field">
                    <label for="legal_request_name">Name</label>
                    <input id="legal_request_name" name="name" type="text" value="{{ old('name') }}" required>
                    @error('name')<p class="muted">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="legal_request_email">Email</label>
                    <input id="legal_request_email" name="email" type="email" value="{{ old('email') }}" required>
                    @error('email')<p class="muted">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid two">
                <div class="field">
                    <label for="legal_request_topic">Topic</label>
                    <select id="legal_request_topic" name="topic" required>
                        <option value="privacy" @selected(old('topic') === 'privacy')>Privacy request</option>
                        <option value="returns" @selected(old('topic') === 'returns')>Returns and refunds</option>
                        <option value="order_support" @selected(old('topic') === 'order_support')>Order support</option>
                        <option value="general" @selected(old('topic', 'general') === 'general')>General</option>
                    </select>
                    @error('topic')<p class="muted">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="legal_request_order_reference">Order reference</label>
                    <input id="legal_request_order_reference" name="order_reference" type="text" value="{{ old('order_reference') }}" placeholder="VF31">
                    @error('order_reference')<p class="muted">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="field">
                <label for="legal_request_message">Message</label>
                <textarea id="legal_request_message" name="message" rows="6" required>{{ old('message') }}</textarea>
                @error('message')<p class="muted">{{ $message }}</p>@enderror
            </div>

            <div class="actions">
                <button type="submit">Send request</button>
            </div>
        </form>
    </section>
@endsection
