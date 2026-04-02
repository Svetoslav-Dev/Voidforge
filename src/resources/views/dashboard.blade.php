@extends('layouts.app', ['title' => __('account.page_title')])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">{{ __('account.heading') }}</h1>
            <p class="lead">{{ __('account.lead') }}</p>
        </div>

        <div class="grid two">
            <div class="card">
                <h2 style="color: #4ecba3;">{{ __('account.section_account') }}</h2>
                <p>{{ auth()->user()->name }}</p>
                <p class="muted">{{ __('ui.signed_in_as', ['email' => auth()->user()->email]) }}</p>

                @if ($defaultShippingAddress)
                    <div style="margin-top: 1rem;">
                        <p style="margin: 0 0 0.35rem; font-weight: 700;">{{ __('account.default_shipping_address') }}</p>
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
                        <p style="margin: 0 0 0.35rem; font-weight: 700;">{{ __('account.default_saved_card') }}</p>
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
                        <a class="button secondary" href="{{ route('account.addresses.index') }}">{{ __('account.saved_shipping_addresses') }}</a>
                        <a class="button secondary" href="{{ route('account.payment-methods.index') }}">{{ __('account.saved_cards') }}</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="color: #4ecba3;">{{ __('account.section_receipts') }}</h2>
                <p class="muted">{{ __('account.receipts_hint') }}</p>
                <div class="account-card-toolbar">
                    <div class="actions">
                        <a class="button secondary" href="{{ route('orders.index') }}">{{ __('account.open_receipts') }}</a>
                    </div>

                    <form method="POST" action="{{ route('account.destroy') }}" data-account-delete-form>
                        @csrf
                        @method('DELETE')
                        <button type="button" class="button danger" data-account-delete-open>{{ __('account.delete_account') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="account-delete-modal" data-account-delete-modal hidden>
        <div class="account-delete-modal__backdrop" data-account-delete-close></div>
        <div class="card account-delete-modal__panel" role="dialog" aria-modal="true" aria-labelledby="account-delete-title">
            <p class="muted">{{ __('account.delete_account') }}</p>
            <h2 id="account-delete-title">{{ __('account.delete_account_title') }}</h2>
            <p class="muted">
                {!! __('account.delete_account_subtitle', ['phrase' => '<strong>'.__('account.delete_account_confirm_phrase').'</strong>']) !!}
            </p>

            <div class="field">
                <label for="account-delete-confirmation">{{ __('account.delete_account_label') }}</label>
                <input
                    id="account-delete-confirmation"
                    type="text"
                    autocomplete="off"
                    spellcheck="false"
                    data-account-delete-input
                    placeholder="{{ __('account.delete_account_confirm_phrase') }}"
                >
            </div>

            <div class="actions account-delete-modal__actions">
                <button type="button" class="button secondary" data-account-delete-close>{{ __('ui.cancel') }}</button>
                <button type="button" class="button danger" data-account-delete-submit disabled>{{ __('ui.delete') }}</button>
            </div>
        </div>
    </div>
@endsection
