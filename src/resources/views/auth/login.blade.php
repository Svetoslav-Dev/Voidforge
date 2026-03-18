@extends('layouts.app', ['title' => 'Login | Voidforge'])

@section('content')
    <div class="auth-wrap">
        <section class="card auth-card">
            <h1>Welcome back</h1>
            <p>Sign in to manage your account, orders, and checkout details.</p>

            @if ($errors->any())
                <div class="errors">
                    <strong>Login failed.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                </div>

                <label class="checkbox" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1">
                    <span>Remember me</span>
                </label>

                <button type="submit">Login</button>
            </form>
        </section>
    </div>
@endsection
