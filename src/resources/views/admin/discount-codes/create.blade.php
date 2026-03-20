@extends('layouts.app', ['title' => 'Create Discount Code | Admin'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Create discount code</h1>
            <p class="lead">Add a new promotion for the storefront cart and checkout flow.</p>
        </div>
    </section>

    <section class="card" style="margin-top: 1rem;">
        <form method="POST" action="{{ route('admin.discount-codes.store') }}">
            @csrf
            @include('admin.discount-codes.form-fields')

            <div class="actions">
                <a class="button secondary" href="{{ route('admin.discount-codes.index') }}">Back to admin</a>
                <button type="submit">Create code</button>
            </div>
        </form>
    </section>
@endsection
