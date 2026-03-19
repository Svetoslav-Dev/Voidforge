@extends('layouts.app', ['title' => 'Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <h1>Dark goods for a sharper catalog</h1>
            <p class="lead">
                Voidforge is a dark-themed shirt storefront built for browsing items, managing accounts,
                checking out with hosted payments, and handling catalog and order operations through an admin area.
            </p>
        </div>

        <div class="grid two">
            <div class="card card-plain">
                <h2>Current stack</h2>
                <p class="muted">Laravel, MariaDB, Docker, TypeScript, Stripe, and PayPal powering a shirt storefront with account, cart, shipping, checkout, receipts, and admin flows.</p>
            </div>

            <div class="card card-plain">
                <h2>Security baseline</h2>
                <p class="muted">Validated forms, Laravel auth, CSRF protection, password hashing, hosted payments, and server-side order handling.</p>
            </div>
        </div>
    </section>

@endsection
