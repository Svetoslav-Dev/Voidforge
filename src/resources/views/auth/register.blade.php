@extends('layouts.app', ['title' => 'Register | Voidforge'])

@section('content')
    <div class="auth-wrap">
        <section class="card auth-card">
            <h1>Create your account</h1>
            <p>Register with a secure password so you can shop, track orders, and return to checkout later.</p>

            @if ($errors->any())
                <div class="errors">
                    <strong>Registration failed.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password">
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>

                <button type="submit">Create account</button>
            </form>
        </section>
    </div>
@endsection
