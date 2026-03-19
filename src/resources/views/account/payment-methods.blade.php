@extends('layouts.app', ['title' => 'Saved Cards | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Saved Cards</h1>
            <p class="lead">
                Add cards through Stripe's hosted flow. Voidforge stores only masked card details for display.
            </p>
        </div>

        <div class="account-addresses-toolbar">
            <a class="button secondary" href="{{ route('dashboard') }}">Back to my account</a>
            @if ($stripeConfigured)
                <form method="POST" action="{{ route('account.payment-methods.store') }}">
                    @csrf
                    <button type="submit">Add card with Stripe</button>
                </form>
            @endif
        </div>

        @if (! $stripeConfigured)
            <div class="card" style="margin-top: 1rem;">
                <p class="muted">Stripe is not configured in this environment yet.</p>
            </div>
        @endif

        <section class="account-address-grid">
            @forelse ($paymentMethods as $paymentMethod)
                <article class="card account-address-card">
                    <p style="margin: 0 0 0.3rem; font-weight: 700;">
                        {{ strtoupper($paymentMethod->brand ?? 'Card') }} ending in {{ $paymentMethod->last4 ?? '----' }}
                        @if ($paymentMethod->is_default)
                            <span style="color: #ffb86b;">· Default</span>
                        @endif
                    </p>
                    <p class="muted" style="margin: 0;">
                        Expires {{ str_pad((string) ($paymentMethod->exp_month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $paymentMethod->exp_year ?? '----' }}
                    </p>
                    <p class="muted" style="margin: 0;">Provider: {{ strtoupper($paymentMethod->provider) }}</p>
                    <div class="actions" style="margin-top: auto;">
                        @unless ($paymentMethod->is_default)
                            <form method="POST" action="{{ route('account.payment-methods.default', $paymentMethod) }}">
                                @csrf
                                @method('PATCH')
                                <button class="button secondary address-default-button" type="submit">Set as default</button>
                            </form>
                        @endunless

                        <button
                            type="button"
                            class="button secondary address-delete-button"
                            data-payment-method-delete-open
                            data-delete-url="{{ route('account.payment-methods.destroy', $paymentMethod) }}"
                            data-payment-label="{{ strtoupper($paymentMethod->brand ?? 'Card') }} ending in {{ $paymentMethod->last4 ?? '----' }}"
                        >
                            Delete card
                        </button>
                    </div>
                </article>
            @empty
                <article class="card">
                    <p class="muted">No saved cards yet.</p>
                </article>
            @endforelse
        </section>
    </section>

    <div class="account-address-modal" data-payment-method-delete-modal hidden>
        <div class="account-address-modal__backdrop" data-payment-method-delete-close></div>
        <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="payment-method-delete-title">
            <h2 id="payment-method-delete-title" style="margin-top: 0;">Delete saved card</h2>
            <p class="muted" data-payment-method-delete-copy>Are you sure you want to remove this saved card?</p>

            <form method="POST" action="" data-payment-method-delete-form>
                @csrf
                @method('DELETE')
                <div class="actions account-address-modal__actions">
                    <button type="button" class="button secondary" data-payment-method-delete-close>No</button>
                    <button type="submit" class="button danger">Yes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
