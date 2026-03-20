@extends('layouts.app', ['title' => 'Saved Shipping Addresses | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">Saved Shipping Addresses</h1>
            <p class="lead">
                Keep your delivery details ready here and reuse them during checkout.
            </p>
        </div>

        <div class="account-addresses-toolbar">
            <a class="button secondary" href="{{ route('dashboard') }}">Back to my account</a>
            <button type="button" class="button" data-address-create-open>Add shipping address</button>
        </div>

        <section class="account-address-grid">
            @forelse ($shippingAddresses as $address)
                <article class="card account-address-card" data-address-card data-address-id="{{ $address->id }}">
                    <p style="margin: 0 0 0.3rem; font-weight: 700;">
                        {{ $address->label }}
                        <span
                            style="color: #ffb86b;{{ $address->is_default ? '' : ' display:none;' }}"
                            data-address-default-badge
                        >· Default</span>
                    </p>
                    <p class="muted" style="margin: 0;">{{ $address->recipient_name }}</p>
                    <p class="muted" style="margin: 0;">
                        {{ $address->address_line_1 }}
                        @if ($address->address_line_2)
                            , {{ $address->address_line_2 }}
                        @endif
                        , {{ $address->city }}
                        @if ($address->state)
                            , {{ $address->state }}
                        @endif
                        , {{ $address->postal_code }}
                        , {{ $address->country }}
                    </p>
                    @if ($address->phone)
                        <p class="muted" style="margin: 0;">{{ $address->phone }}</p>
                    @endif
                    <div class="actions" style="margin-top: auto;">
                        <button
                            type="button"
                            class="button secondary"
                            data-address-edit-open
                            data-update-url="{{ route('account.addresses.update', $address) }}"
                            data-label="{{ $address->label }}"
                            data-recipient-name="{{ $address->recipient_name }}"
                            data-phone="{{ $address->phone }}"
                            data-address-line-1="{{ $address->address_line_1 }}"
                            data-address-line-2="{{ $address->address_line_2 }}"
                            data-city="{{ $address->city }}"
                            data-state="{{ $address->state }}"
                            data-postal-code="{{ $address->postal_code }}"
                            data-country="{{ $address->country }}"
                            data-is-default="{{ $address->is_default ? '1' : '0' }}"
                        >
                            Edit address
                        </button>
                        <form
                            method="POST"
                            action="{{ route('account.addresses.default', $address) }}"
                            data-address-default-form
                            @if ($address->is_default) hidden @endif
                        >
                            @csrf
                            @method('PATCH')
                            <button class="button secondary address-default-button" type="submit">Set as default</button>
                        </form>
                        <button
                            type="button"
                            class="button secondary address-delete-button"
                            data-address-delete-open
                            data-delete-url="{{ route('account.addresses.destroy', $address) }}"
                            data-address-label="{{ $address->label }}"
                        >
                            Delete address
                        </button>
                    </div>
                </article>
            @empty
                <article class="card">
                    <p class="muted">No saved shipping addresses yet.</p>
                </article>
            @endforelse
        </section>
    </section>

    <div class="account-address-modal" data-address-create-modal hidden>
        <div class="account-address-modal__backdrop" data-address-create-close></div>
        <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="address-create-title">
            <h2 id="address-create-title" style="margin-top: 0;">Add shipping address</h2>
            <p class="muted">Create a saved delivery address without leaving the page.</p>

            @include('account._form', [
                'formAction' => route('account.addresses.store'),
                'submitLabel' => 'Save address',
                'modalMode' => 'create',
            ])
        </div>
    </div>

    <div class="account-address-modal" data-address-edit-modal hidden>
        <div class="account-address-modal__backdrop" data-address-edit-close></div>
        <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="address-edit-title">
            <h2 id="address-edit-title" style="margin-top: 0;">Edit shipping address</h2>
            <p class="muted">Update this saved delivery address without leaving the page.</p>

            @include('account._form', [
                'formAction' => '',
                'formMethod' => 'PATCH',
                'submitLabel' => 'Update address',
                'modalMode' => 'edit',
            ])
        </div>
    </div>

    <div class="account-address-modal" data-address-delete-modal hidden>
        <div class="account-address-modal__backdrop" data-address-delete-close></div>
        <div class="card account-address-modal__panel" role="dialog" aria-modal="true" aria-labelledby="address-delete-title">
            <h2 id="address-delete-title" style="margin-top: 0;">Delete shipping address</h2>
            <p class="muted" data-address-delete-copy>Are you sure you want to archive this address?</p>

            <form method="POST" action="" data-address-delete-form>
                @csrf
                @method('DELETE')
                <div class="actions account-address-modal__actions">
                    <button type="button" class="button secondary" data-address-delete-close>No</button>
                    <button type="submit" class="button danger">Yes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
