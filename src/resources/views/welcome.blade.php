@extends('layouts.app', ['title' => 'Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Voidborn Apparel</p>
            <h1>Dark goods for a sharper catalog.</h1>
            <p class="lead">
                Voidforge is running with session-based authentication, MariaDB-backed data, and a fuller
                demo shirt lineup. The storefront now carries the darker visual direction the name calls for,
                alongside cart, checkout, Stripe, and PayPal approval flows.
            </p>
        </div>

        <div class="actions">
            <a class="button" href="{{ route('products.index') }}">Browse shirts</a>
        </div>

        <div class="grid two">
            <div class="card">
                <h2>Current stack</h2>
                <p class="muted">Laravel 13, MariaDB, Docker, session auth, and a seeded t-shirt storefront.</p>
            </div>

            <div class="card">
                <h2>Security baseline</h2>
                <p class="muted">CSRF protection, Laravel password hashing, validated auth requests, and server-side sessions.</p>
            </div>
        </div>
    </section>
@endsection
