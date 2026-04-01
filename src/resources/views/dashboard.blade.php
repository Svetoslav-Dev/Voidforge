@extends('layouts.app', ['title' => 'My Account | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">My Account</h1>
            <p class="lead">
                Review your purchase history, open receipts, and keep your account flow separate from the admin tools.
            </p>
        </div>

        <div class="grid two">
            <div class="card">
                <h2 style="color: #4ecba3;">Account</h2>
                <p>{{ auth()->user()->name }}</p>
                <p class="muted">Signed in as {{ auth()->user()->email }}</p>

                @if ($defaultShippingAddress)
                    <div style="margin-top: 1rem;">
                        <p style="margin: 0 0 0.35rem; font-weight: 700;">Default shipping address</p>
                        <p class="muted" style="margin: 0;">{{ $defaultShippingAddress->label }}</p>
                        <p class="muted" style="margin: 0;">{{ $defaultShippingAddress->recipient_name }}</p>
                        <p class="muted" style="margin: 0;">
                            {{ $defaultShippingAddress->address_line_1 }},
                            {{ $defaultShippingAddress->city }},
                            {{ $defaultShippingAddress->postal_code }},
                            {{ $defaultShippingAddress->country }}
                        </p>
                    </div>
                @endif

                @if ($defaultSavedCard)
                    <div style="margin-top: 1rem;">
                        <p style="margin: 0 0 0.35rem; font-weight: 700;">Default saved card</p>
                        <p class="muted" style="margin: 0;">
                            {{ strtoupper($defaultSavedCard->brand ?? 'Card') }} ending in {{ $defaultSavedCard->last4 ?? '----' }}
                        </p>
                        <p class="muted" style="margin: 0;">
                            Expires {{ str_pad((string) ($defaultSavedCard->exp_month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $defaultSavedCard->exp_year ?? '----' }}
                        </p>
                    </div>
                @endif

                <div class="account-card-toolbar">
                    <div class="actions">
                        <a class="button secondary" href="{{ route('account.addresses.index') }}">Saved shipping addresses</a>
                        <a class="button secondary" href="{{ route('account.payment-methods.index') }}">Saved cards</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="color: #4ecba3;">Receipts</h2>
                <p class="muted">Open previous orders to review purchased shirts, totals, and payment details.</p>
                <div class="account-card-toolbar">
                    <div class="actions">
                        <a class="button secondary" href="{{ route('orders.index') }}">Open receipts</a>
                    </div>

                    <form method="POST" action="{{ route('account.destroy') }}" data-account-delete-form>
                        @csrf
                        @method('DELETE')
                        <button type="button" class="button danger" data-account-delete-open>Delete account</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="account-delete-modal" data-account-delete-modal hidden>
        <div class="account-delete-modal__backdrop" data-account-delete-close></div>
        <div class="card account-delete-modal__panel" role="dialog" aria-modal="true" aria-labelledby="account-delete-title">
            <p class="muted">Delete account</p>
            <h2 id="account-delete-title">Confirm account deletion</h2>
            <p class="muted">
                This will sign you out and archive your account. Type <strong>I am sure</strong> to enable deletion.
            </p>

            <div class="field">
                <label for="account-delete-confirmation">Confirmation text</label>
                <input
                    id="account-delete-confirmation"
                    type="text"
                    autocomplete="off"
                    spellcheck="false"
                    data-account-delete-input
                    placeholder="I am sure"
                >
            </div>

            <div class="actions account-delete-modal__actions">
                <button type="button" class="button secondary" data-account-delete-close>Cancel</button>
                <button type="button" class="button danger" data-account-delete-submit disabled>Delete</button>
            </div>
        </div>
    </div>
@endsection
