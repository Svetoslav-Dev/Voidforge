@extends('layouts.app', ['title' => 'Edit Discount Code | Admin'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Edit {{ $discountCode->code }}</h1>
            <p class="lead">Update discount rules, amount, expiry, and active state.</p>
        </div>
    </section>

    <section class="card" style="margin-top: 1rem;">
        <form method="POST" action="{{ route('admin.discount-codes.update', $discountCode) }}">
            @csrf
            @method('PATCH')
            @include('admin.discount-codes.form-fields', ['discountCode' => $discountCode])

            <div class="actions">
                <a class="button secondary" href="{{ route('admin.discount-codes.index') }}">Back to admin</a>
                <button type="submit">Save code</button>
            </div>
        </form>
    </section>
@endsection
