@extends('layouts.app', ['title' => 'Edit Shipping Address | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Account Tools</p>
            <h1>Edit Shipping Address</h1>
            <p class="lead">
                Update this saved delivery address and keep your checkout details current.
            </p>
        </div>

        <div class="actions">
            <a class="button secondary" href="{{ route('account.addresses.index') }}">Back to saved shipping addresses</a>
        </div>

        <div class="card">
            @include('account._form', [
                'formAction' => route('account.addresses.update', $shippingAddress),
                'formMethod' => 'PATCH',
                'submitLabel' => 'Update address',
                'shippingAddress' => $shippingAddress,
            ])
        </div>
    </section>
@endsection
