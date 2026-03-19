@extends('layouts.app', ['title' => 'Saved Shipping Addresses | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Account Tools</p>
            <h1>Saved Shipping Addresses</h1>
            <p class="lead">
                Keep your delivery details ready here and reuse them during checkout.
            </p>
        </div>

        <div class="actions">
            <a class="button secondary" href="{{ route('dashboard') }}">Back to my account</a>
        </div>

        <div class="grid two">
            <div class="card">
                <h2>Saved Shipping Addresses</h2>
                @forelse ($shippingAddresses as $address)
                    <div style="padding: 0.9rem 0; border-top: 1px solid rgba(136, 156, 211, 0.22);">
                        <p style="margin: 0 0 0.3rem; font-weight: 700;">
                            {{ $address->label }}
                            @if ($address->is_default)
                                <span style="color: #ffb86b;">· Default</span>
                            @endif
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
                        <div class="actions" style="margin-top: 0.8rem;">
                            <a class="button secondary" href="{{ route('account.addresses.edit', $address) }}">Edit address</a>
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                                @csrf
                                @method('DELETE')
                                <button class="button secondary" type="submit">Delete address</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="muted">No saved shipping addresses yet.</p>
                @endforelse
            </div>

            <div class="card">
                <h2>Add Shipping Address</h2>
                @include('account._form', [
                    'formAction' => route('account.addresses.store'),
                    'submitLabel' => 'Save address',
                ])
            </div>
        </div>
    </section>
@endsection
