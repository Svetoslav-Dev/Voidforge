<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $rawTitle = trim((string) ($title ?? 'VoidForgeStore'));
            $baseTitle = '🟣 VoidForgeStore';
            $pageTitle = trim(str_replace(['| VoidForgeStore', '| Voidforge', '| Admin'], '', $rawTitle));
            $documentTitle = $pageTitle === '' || in_array($pageTitle, ['VoidForgeStore', 'Voidforge'], true)
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
                        <span class="brand-text">{{ __('ui.brand') }}</span>
                    </a>
                    <a class="button secondary" href="{{ route('products.index') }}">{{ __('ui.browse_shirts') }}</a>
                    @auth
                        @if (! auth()->user()->is_admin)
                            <a class="button secondary" href="{{ route('dashboard') }}">{{ __('ui.my_account') }}</a>
                        @else
                            <a class="button secondary" href="{{ route('dashboard') }}">{{ __('ui.my_account') }}</a>
                            <a class="button secondary" href="{{ route('admin.panel') }}">{{ __('ui.admin') }}</a>
                        @endif
                    @endauth
                    @if (($cartItemCount ?? 0) > 0)
                        <a class="button secondary" href="{{ route('cart.index') }}" data-cart-link>{{ __('ui.cart_count', ['count' => $cartItemCount]) }}</a>
                    @endif
                </div>

                <div class="nav-links">
                    @auth
                        <details class="account-menu">
                            <summary class="button account-menu__toggle">{{ auth()->user()->name }}</summary>
                            <div class="card account-menu__panel">
                                <p class="muted account-menu__label">{{ __('ui.signed_in_as', ['email' => auth()->user()->email]) }}</p>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">{{ __('ui.logout') }}</button>
                                </form>
                            </div>
                        </details>
                    @else
                        <button class="button secondary" type="button" data-auth-modal-trigger="login">{{ __('ui.login') }}</button>
                        <button class="button" type="button" data-auth-modal-trigger="register">{{ __('ui.create_account') }}</button>
                    @endauth

                    <form method="POST" action="{{ route('language.switch') }}" class="language-switcher">
                        @csrf
                        <button type="submit" name="locale" value="en" class="button secondary language-switcher__btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</button>
                        <button type="submit" name="locale" value="bg" class="button secondary language-switcher__btn {{ app()->getLocale() === 'bg' ? 'is-active' : '' }}">BG</button>
                    </form>
                </div>
            </nav>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <div class="cookie-consent" data-cookie-consent hidden>
                <div class="cookie-consent__backdrop"></div>

                <div class="card cookie-consent__panel">
                    <div class="cookie-consent__copy">
                        <p class="cookie-consent__title">{{ __('cookies.title') }}</p>
                        <p class="muted">{{ __('cookies.intro') }}</p>

                        <div class="cookie-consent__options">
                            <div class="cookie-consent__option">
                                <strong>{{ __('cookies.essential_only_title') }}</strong>
                                <span class="muted">{{ __('cookies.essential_only_description') }}</span>
                            </div>
                            <div class="cookie-consent__option">
                                <strong>{{ __('cookies.accept_all_title') }}</strong>
                                <span class="muted">{{ __('cookies.accept_all_description') }}</span>
                            </div>
                            <div class="cookie-consent__option">
                                <strong>{{ __('cookies.preferences_title') }}</strong>
                                <span class="muted">{{ __('cookies.preferences_description') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="cookie-consent__actions">
                        <button class="button secondary" type="button" data-cookie-consent-open-preferences>{{ __('cookies.btn_preferences') }}</button>
                        <button class="button secondary" type="button" data-cookie-consent-reject>{{ __('cookies.btn_essential_only') }}</button>
                        <button class="button" type="button" data-cookie-consent-accept>{{ __('cookies.btn_accept_all') }}</button>
                    </div>
                </div>
            </div>

            <div class="cookie-preferences-modal" data-cookie-preferences-modal hidden>
                <div class="cookie-preferences-modal__backdrop" data-cookie-preferences-close></div>

                <section class="card cookie-preferences-modal__panel">
                    <div class="cookie-preferences-modal__header">
                        <div>
                            <h2>{{ __('cookies.modal_title') }}</h2>
                            <p class="muted">{{ __('cookies.modal_subtitle') }}</p>
                        </div>
                        <button class="button danger" type="button" data-cookie-preferences-close>{{ __('cookies.modal_close') }}</button>
                    </div>

                    <div class="cookie-preferences-modal__body">
                        <label class="cookie-preferences-option">
                            <span>
                                <strong>{{ __('cookies.essential_title') }}</strong>
                                <span class="muted">{{ __('cookies.essential_description') }}</span>
                            </span>
                            <input type="checkbox" checked disabled>
                        </label>

                        <label class="cookie-preferences-option">
                            <span>
                                <strong>{{ __('cookies.optional_title') }}</strong>
                                <span class="muted">{{ __('cookies.optional_description') }}</span>
                            </span>
                            <input type="checkbox" data-cookie-preferences-optional>
                        </label>
                    </div>

                    <div class="cookie-preferences-modal__actions">
                        <button class="button secondary" type="button" data-cookie-preferences-essential>{{ __('cookies.btn_use_essential') }}</button>
                        <button class="button" type="button" data-cookie-preferences-save>{{ __('cookies.btn_save') }}</button>
                    </div>
                </section>
            </div>

            @yield('content')

            @guest
                <div
                    class="auth-modal @if ($authModal !== 'login') is-hidden @endif"
                    data-auth-modal-root="login"
                    @if ($authModal === 'login') data-auth-modal-open="true" @endif
                >
                    <div class="auth-modal__backdrop" data-auth-modal-close></div>

                    <section class="card auth-modal__panel is-active">
                        <div class="auth-modal__header">
                            <div>
                                <h2>{{ __('auth.login_title') }}</h2>
                                <p class="muted">{{ __('auth.login_subtitle') }}</p>
                            </div>
                            <button class="button danger" type="button" data-auth-modal-close>{{ __('ui.close') }}</button>
                        </div>

                        @if ($authModal === 'login' && $errors->any())
                            <div class="errors">
                                <strong>{{ __('auth.login_failed') }}</strong>
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
                                <label for="auth_modal_login_email">{{ __('auth.field_email') }}</label>
                                <input id="auth_modal_login_email" name="email" type="email" value="{{ $authModal === 'login' ? old('email') : (request()->cookie('remember_email') ?? '') }}" required autocomplete="username">
                            </div>

                            <div class="field">
                                <label for="auth_modal_login_password">{{ __('auth.field_password') }}</label>
                                <input id="auth_modal_login_password" name="password" type="password" required autocomplete="current-password">
                            </div>

                            <div class="auth-modal__footer">
                                <label class="checkbox" for="auth_modal_remember">
                                    <input id="auth_modal_remember" name="remember" type="checkbox" value="1" @checked(($authModal === 'login' && old('remember')) || request()->cookie('remember_email'))>
                                    <span>{{ __('auth.remember_me') }}</span>
                                </label>

                                <div class="auth-modal__actions">
                                    <button type="submit">{{ __('auth.login_button') }}</button>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <div
                    class="auth-modal @if ($authModal !== 'register') is-hidden @endif"
                    data-auth-modal-root="register"
                    @if ($authModal === 'register') data-auth-modal-open="true" @endif
                >
                    <div class="auth-modal__backdrop" data-auth-modal-close></div>

                    <section class="card auth-modal__panel is-active">
                        <div class="auth-modal__header">
                            <div>
                                <h2>{{ __('auth.register_title') }}</h2>
                                <p class="muted">{{ __('auth.register_subtitle') }}</p>
                            </div>
                            <button class="button danger" type="button" data-auth-modal-close>{{ __('ui.close') }}</button>
                        </div>

                        @if ($authModal === 'register' && $errors->any())
                            <div class="errors">
                                <strong>{{ __('auth.register_failed') }}</strong>
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
                                <label for="auth_modal_register_name">{{ __('auth.field_name') }}</label>
                                <input id="auth_modal_register_name" name="name" type="text" value="{{ $authModal === 'register' ? old('name') : '' }}" required autocomplete="name">
                            </div>

                            <div class="field">
                                <label for="auth_modal_register_email">{{ __('auth.field_email') }}</label>
                                <input id="auth_modal_register_email" name="email" type="email" value="{{ $authModal === 'register' ? old('email') : '' }}" required autocomplete="username">
                            </div>

                            <div class="field">
                                <label for="auth_modal_register_password">{{ __('auth.field_password') }}</label>
                                <input id="auth_modal_register_password" name="password" type="password" required autocomplete="new-password">
                            </div>

                            <div class="field">
                                <label for="auth_modal_register_password_confirmation">{{ __('auth.field_confirm_password') }}</label>
                                <input id="auth_modal_register_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                            </div>

                            <div class="auth-modal__actions">
                                <button type="submit">{{ __('auth.register_button') }}</button>
                            </div>
                        </form>
                    </section>
                </div>
            @endguest

            <footer class="card site-footer">
                <div class="site-footer__grid">
                    <section class="site-footer__section">
                        <p class="site-footer__title">{{ __('ui.footer_store') }}</p>
                        <a href="{{ route('products.index') }}">{{ __('ui.footer_browse_shirts') }}</a>
                        <a href="{{ route('cart.index') }}">{{ __('ui.footer_cart') }}</a>
                        <a href="{{ route('order.tracking') }}">{{ __('ui.footer_order_tracking') }}</a>
                    </section>

                    <section class="site-footer__section">
                        <p class="site-footer__title">{{ __('ui.footer_legal') }}</p>
                        <a href="{{ route('legal.privacy') }}">{{ __('ui.footer_privacy_policy') }}</a>
                        <a href="{{ route('legal.terms') }}">{{ __('ui.footer_terms') }}</a>
                        <a href="{{ route('legal.returns') }}">{{ __('ui.footer_returns') }}</a>
                        <a href="{{ route('legal.shipping') }}">{{ __('ui.footer_shipping') }}</a>
                        <a href="{{ route('legal.cookies') }}">{{ __('ui.footer_cookies') }}</a>
                    </section>

                    <section class="site-footer__section">
                        <p class="site-footer__title">{{ __('ui.footer_contact') }}</p>
                        <p class="muted">{{ config('legal.support_email') }}</p>
                        <p class="muted">{{ config('legal.support_phone') }}</p>
                        <p class="muted">{{ config('legal.trader_address') }}</p>
                    </section>

                    <section class="site-footer__section">
                        <p class="site-footer__title">{{ __('ui.footer_payments') }}</p>
                        <p class="muted">{{ __('ui.footer_payments_description') }}</p>
                    </section>
                </div>

                <div class="site-footer__bottom">
                    <p class="muted">{{ __('ui.footer_security') }}</p>
                    <div class="site-footer__bottom-links">
                        <button class="button secondary site-footer__button" type="button" data-cookie-consent-open-preferences>{{ __('ui.footer_cookie_preferences') }}</button>
                        <p class="muted">{{ __('ui.footer_copyright', ['year' => now()->year]) }}</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
