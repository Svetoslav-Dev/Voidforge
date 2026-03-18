@extends('layouts.app', ['title' => 'My Account | Voidforge'])

@section('content')
    <section class="card hero">
        <div>
            <p class="muted">Authenticated Area</p>
            <h1>My Account</h1>
            <p class="lead">
                Review your purchase history, open receipts, and keep your account flow separate from the admin tools.
            </p>
        </div>

        <div class="grid two">
            <div class="card">
                <h2>Account</h2>
                <p>{{ auth()->user()->name }}</p>
                <p class="muted">Signed in as {{ auth()->user()->email }}</p>

                @if (auth()->user()->is_admin)
                    <div class="actions">
                        <a class="button secondary" href="{{ route('admin.panel') }}">Open admin panel</a>
                    </div>
                @endif
            </div>

            <div class="card">
                <h2>Receipts</h2>
                <p class="muted">Open previous orders to review purchased shirts, totals, and payment details.</p>
                <div class="actions">
                    <a class="button secondary" href="{{ route('orders.index') }}">Open receipts</a>
                </div>
            </div>
        </div>
    </section>
@endsection
