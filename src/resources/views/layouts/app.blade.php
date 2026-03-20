<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $rawTitle = trim((string) ($title ?? 'Voidforge'));
            $baseTitle = '🟣 Voidforge';
            $pageTitle = trim(str_replace(['| Voidforge', '| Admin'], '', $rawTitle));
            $documentTitle = $pageTitle === '' || $pageTitle === 'Voidforge'
                ? $baseTitle
                : $baseTitle.' | '.$pageTitle;
            $authModal = old('auth_modal');
        @endphp
        <title>{{ $documentTitle }}</title>
        @vite('resources/js/app.ts')
    </head>
    <body>
        <div class="shell">
            <nav class="nav">
                <div class="nav-group" data-nav-group>
                    <a class="brand" href="{{ route('home') }}">
                        <span class="brand-mark" aria-hidden="true"></span>
                        <span class="brand-text">Voidforge</span>
                    </a>
                    <a class="button secondary" href="{{ route('products.index') }}">Browse Shirts</a>
                    @auth
                        @if (! auth()->user()->is_admin)
                            <a class="button secondary" href="{{ route('dashboard') }}">My Account</a>
                        @else
                            <a class="button secondary" href="{{ route('dashboard') }}">My Account</a>
                            <a class="button secondary" href="{{ route('admin.panel') }}">Admin</a>
                        @endif
                    @endauth
                    @if (($cartItemCount ?? 0) > 0)
                        <a class="button secondary" href="{{ route('cart.index') }}" data-cart-link>Cart ({{ $cartItemCount }})</a>
                    @endif
                </div>

                <div class="nav-links">
                    @auth
                        <details class="account-menu">
                            <summary class="button account-menu__toggle">{{ auth()->user()->name }}</summary>
                            <div class="card account-menu__panel">
                                <p class="muted account-menu__label">Signed in as {{ auth()->user()->email }}</p>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">Logout</button>
                                </form>
                            </div>
                        </details>
                    @else
                        <button class="button secondary" type="button" data-auth-modal-trigger="login">Login</button>
                        <button class="button" type="button" data-auth-modal-trigger="register">Create Account</button>
                    @endauth
                </div>
            </nav>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @yield('content')

            @guest
                <div
                    class="auth-modal"
                    data-auth-modal-root="login"
                    @if ($authModal === 'login') data-auth-modal-open="true" @endif
                    hidden
                >
                    <div class="auth-modal__backdrop" data-auth-modal-close></div>

                    <section class="card auth-modal__panel is-active">
                        <div class="auth-modal__header">
                            <div>
                                <h2>Welcome back</h2>
                                <p class="muted">Sign in to manage your account, orders, and checkout details.</p>
                            </div>
                            <button class="button danger" type="button" data-auth-modal-close>Close</button>
                        </div>

                        @if ($authModal === 'login' && $errors->any())
                            <div class="errors">
                                <strong>Login failed.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="auth-modal__form">
                            @csrf
                            <input type="hidden" name="auth_modal" value="login">

                            <div class="field">
                                <label for="auth_modal_login_email">Email</label>
                                <input id="auth_modal_login_email" name="email" type="email" value="{{ $authModal === 'login' ? old('email') : '' }}" required autocomplete="username">
                            </div>

                            <div class="field">
                                <label for="auth_modal_login_password">Password</label>
                                <input id="auth_modal_login_password" name="password" type="password" required autocomplete="current-password">
                            </div>

                            <div class="auth-modal__footer">
                                <label class="checkbox" for="auth_modal_remember">
                                    <input id="auth_modal_remember" name="remember" type="checkbox" value="1" @checked($authModal === 'login' && old('remember'))>
                                    <span>Remember me</span>
                                </label>

                                <div class="auth-modal__actions">
                                    <button type="submit">Login</button>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <div
                    class="auth-modal"
                    data-auth-modal-root="register"
                    @if ($authModal === 'register') data-auth-modal-open="true" @endif
                    hidden
                >
                    <div class="auth-modal__backdrop" data-auth-modal-close></div>

                    <section class="card auth-modal__panel is-active">
                        <div class="auth-modal__header">
                            <div>
                                <h2>Create your account</h2>
                                <p class="muted">Register with a secure password so you can shop, track orders, and return to checkout later.</p>
                            </div>
                            <button class="button danger" type="button" data-auth-modal-close>Close</button>
                        </div>

                        @if ($authModal === 'register' && $errors->any())
                            <div class="errors">
                                <strong>Registration failed.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="auth-modal__form">
                            @csrf
                            <input type="hidden" name="auth_modal" value="register">

                            <div class="field">
                                <label for="auth_modal_register_name">Name</label>
                                <input id="auth_modal_register_name" name="name" type="text" value="{{ $authModal === 'register' ? old('name') : '' }}" required autocomplete="name">
                            </div>

                            <div class="field">
                                <label for="auth_modal_register_email">Email</label>
                                <input id="auth_modal_register_email" name="email" type="email" value="{{ $authModal === 'register' ? old('email') : '' }}" required autocomplete="username">
                            </div>

                            <div class="field">
                                <label for="auth_modal_register_password">Password</label>
                                <input id="auth_modal_register_password" name="password" type="password" required autocomplete="new-password">
                            </div>

                            <div class="field">
                                <label for="auth_modal_register_password_confirmation">Confirm password</label>
                                <input id="auth_modal_register_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                            </div>

                            <div class="auth-modal__actions">
                                <button type="submit">Create Account</button>
                            </div>
                        </form>
                    </section>
                </div>
            @endguest

            <footer class="card site-footer">
                <div class="site-footer__grid">
                    <section class="site-footer__section">
                        <p class="site-footer__title">Voidforge</p>
                        <p class="muted">Void-themed shirts, secure checkout, and account tools built for a compact storefront experience.</p>
                    </section>

                    <section class="site-footer__section">
                        <p class="site-footer__title">Store</p>
                        <a href="{{ route('products.index') }}">Browse shirts</a>
                        <a href="{{ route('cart.index') }}">Cart</a>
                        <a href="{{ route('home') }}">Voidforge info</a>
                    </section>

                    <section class="site-footer__section">
                        <p class="site-footer__title">Contact</p>
                        <p class="muted">support@voidforge.test</p>
                        <p class="muted">+359 2 555 0142</p>
                        <p class="muted">13 Void Circuit, Sofia</p>
                    </section>

                    <section class="site-footer__section">
                        <p class="site-footer__title">Payments</p>
                        <p class="muted">Stripe and PayPal hosted payments. Card data is never stored locally.</p>
                    </section>
                </div>

                <div class="site-footer__bottom">
                    <p class="muted">Secure checkout with Laravel validation, CSRF protection, hashed passwords, and server-side order handling.</p>
                    <p class="muted">© {{ now()->year }} Voidforge</p>
                </div>
            </footer>
        </div>
    </body>
</html>
