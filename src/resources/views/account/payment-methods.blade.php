@extends('layouts.app', ['title' => 'Saved Cards | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Account Tools</p>
            <h1>Saved Cards</h1>
            <p class="lead">
                Add cards through Stripe's hosted flow. Voidforge stores only masked card details for display.
            </p>
        </div>

        <div class="actions">
            <a class="button secondary" href="{{ route('dashboard') }}">Back to my account</a>
        </div>

        <div class="grid two">
            <div class="card">
                <h2>Saved Cards</h2>
                @forelse ($paymentMethods as $paymentMethod)
                    <div style="padding: 0.9rem 0; border-top: 1px solid rgba(136, 156, 211, 0.22);">
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
                        <div class="actions" style="margin-top: 0.8rem;">
                            @unless ($paymentMethod->is_default)
                                <form method="POST" action="{{ route('account.payment-methods.default', $paymentMethod) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="button secondary" type="submit">Set as default</button>
                                </form>
                            @endunless

                            <form method="POST" action="{{ route('account.payment-methods.destroy', $paymentMethod) }}">
                                @csrf
                                @method('DELETE')
                                <button class="button secondary" type="submit">Delete card</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="muted">No saved cards yet.</p>
                @endforelse
            </div>

            <div class="card">
                <h2>Add Credit Card</h2>
                <p class="muted">
                    New cards are added on Stripe's hosted page. Voidforge never stores raw card numbers or CVC values.
                </p>

                @if ($stripeConfigured)
                    <form method="POST" action="{{ route('account.payment-methods.store') }}">
                        @csrf
                        <div class="actions">
                            <button type="submit">Add card with Stripe</button>
                        </div>
                    </form>
                @else
                    <p class="muted">Stripe is not configured in this environment yet.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
