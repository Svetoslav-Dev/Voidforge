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
        @endphp
        <title>{{ $documentTitle }}</title>
        @vite('resources/js/app.ts')
        <style>
            :root {
                color-scheme: dark;
                --bg: #050816;
                --bg-deep: #02040d;
                --surface: #0c1326;
                --surface-strong: #121b34;
                --text: #edf3ff;
                --muted: #8e99b8;
                --line: rgba(136, 156, 211, 0.22);
                --accent: #5be7ff;
                --accent-dark: #27c7e8;
                --accent-soft: rgba(91, 231, 255, 0.12);
                --success: #7cf7c2;
                --danger: #ff8198;
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                font-family: "Trebuchet MS", "Segoe UI", sans-serif;
                background:
                    radial-gradient(circle at top, rgba(91, 231, 255, 0.16) 0, transparent 26%),
                    radial-gradient(circle at 80% 10%, rgba(131, 95, 255, 0.12) 0, transparent 22%),
                    linear-gradient(180deg, #081022 0%, var(--bg) 38%, var(--bg-deep) 100%);
                color: var(--text);
                min-height: 100vh;
            }

            a { color: inherit; }

            .shell {
                width: min(1080px, calc(100% - 2rem));
                margin: 0 auto;
                padding: 1.25rem 0 3rem;
            }

            .nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                padding: 1rem 0 1.5rem;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                font-size: 1.3rem;
                font-weight: 700;
                letter-spacing: 0.18em;
                text-decoration: none;
                text-transform: uppercase;
                text-shadow: 0 0 16px rgba(91, 231, 255, 0.24);
            }

            .brand-mark {
                position: relative;
                width: 1.1rem;
                height: 1.1rem;
                border-radius: 999px;
                border: 1px solid rgba(171, 126, 255, 0.56);
                box-shadow: inset 0 0 18px rgba(171, 126, 255, 0.18), 0 0 18px rgba(171, 126, 255, 0.24);
                background:
                    radial-gradient(circle at center, rgba(2, 4, 13, 0.96) 0 28%, rgba(171, 126, 255, 0.3) 29% 52%, rgba(2, 4, 13, 0.92) 53% 100%);
            }

            .brand-mark::after {
                content: "";
                position: absolute;
                inset: 0.23rem;
                border-radius: 999px;
                border: 1px solid rgba(209, 185, 255, 0.34);
            }

            .brand-text {
                line-height: 1;
            }

            .nav-links {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .nav-group {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .card {
                background:
                    linear-gradient(180deg, rgba(18, 27, 52, 0.96) 0%, rgba(12, 19, 38, 0.96) 100%);
                border: 1px solid var(--line);
                border-radius: 20px;
                padding: 1.5rem;
                box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
                backdrop-filter: blur(12px);
            }

            .hero { display: grid; gap: 1.5rem; }

            .hero h1,
            .auth-card h1 {
                margin: 0 0 0.75rem;
                font-size: clamp(2rem, 5vw, 3.6rem);
                line-height: 1;
            }

            .lead {
                margin: 0;
                max-width: 46rem;
                color: var(--muted);
                font-size: 1.05rem;
                line-height: 1.6;
            }

            .actions {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                margin-top: 1rem;
            }

            .button,
            button {
                appearance: none;
                border: 1px solid transparent;
                border-radius: 999px;
                background: var(--accent);
                color: #041018;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font: inherit;
                font-weight: 700;
                min-height: 2.2rem;
                padding: 0.45rem 0.95rem;
                line-height: 1.15;
                text-decoration: none;
                transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
                box-shadow: 0 0 0 1px rgba(91, 231, 255, 0.12), 0 10px 24px rgba(39, 199, 232, 0.18);
            }

            .button:hover,
            button:hover {
                background: var(--accent-dark);
                transform: translateY(-1px);
                box-shadow: 0 0 0 1px rgba(91, 231, 255, 0.22), 0 14px 30px rgba(39, 199, 232, 0.25);
            }

            .button.secondary {
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid var(--line);
                color: var(--text);
                box-shadow: none;
            }

            .button.danger,
            button.danger {
                background: rgba(255, 129, 152, 0.14);
                border-color: rgba(255, 129, 152, 0.38);
                color: #ffd7df;
                box-shadow: 0 0 0 1px rgba(255, 129, 152, 0.12), 0 10px 24px rgba(99, 19, 40, 0.24);
            }

            .button.danger:hover,
            button.danger:hover {
                background: rgba(255, 129, 152, 0.2);
                border-color: rgba(255, 129, 152, 0.54);
                box-shadow: 0 0 0 1px rgba(255, 129, 152, 0.18), 0 14px 30px rgba(99, 19, 40, 0.3);
            }

            .button:disabled,
            button:disabled {
                opacity: 0.45;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }

            .auth-wrap {
                display: grid;
                place-items: center;
                min-height: calc(100vh - 7rem);
            }

            .auth-card { width: min(100%, 34rem); }

            .auth-card p,
            .muted {
                color: var(--muted);
                line-height: 1.5;
            }

            .field { margin-bottom: 1rem; }

            .field label {
                display: block;
                font-weight: 700;
                margin-bottom: 0.35rem;
            }

            .field input {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: 12px;
                padding: 0.85rem 0.95rem;
                font: inherit;
                background: rgba(3, 8, 20, 0.8);
                color: var(--text);
            }

            .field select,
            .field textarea {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: 12px;
                padding: 0.85rem 0.95rem;
                font: inherit;
                background: rgba(3, 8, 20, 0.8);
                color: var(--text);
            }

            .checkbox {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin: 1rem 0 1.25rem;
            }

            .checkbox input { width: auto; }

            .errors,
            .status {
                border-radius: 12px;
                margin-bottom: 1rem;
                padding: 0.9rem 1rem;
            }

            .errors {
                background: rgba(255, 129, 152, 0.09);
                border: 1px solid rgba(255, 129, 152, 0.28);
                color: var(--danger);
            }

            .status {
                background: rgba(124, 247, 194, 0.1);
                border: 1px solid rgba(124, 247, 194, 0.28);
                color: var(--success);
            }

            .grid {
                display: grid;
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .grid.two {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }

            .grid.three {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .chip-row {
                display: flex;
                flex-wrap: wrap;
                gap: 0.6rem;
            }

            .chip {
                border: 1px solid var(--line);
                border-radius: 999px;
                padding: 0.55rem 0.9rem;
                text-decoration: none;
                background: rgba(255, 255, 255, 0.03);
            }

            .chip.active {
                background: var(--accent);
                border-color: var(--accent);
                color: white;
            }

            .product-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                margin-top: 1.5rem;
            }

            .product-card,
            .product-show {
                display: grid;
                gap: 1rem;
            }

            .product-show-visual {
                min-height: 192px;
                max-width: 30rem;
            }

            .product-show-panels {
                margin-top: 0.5rem;
                gap: 0.8rem;
            }

            .product-show-panels .card {
                padding: 1.15rem;
            }

            .product-show-top {
                display: grid;
                grid-template-columns: minmax(0, 30rem) minmax(0, 1fr);
                gap: 0.8rem;
                align-items: start;
            }

            .product-show-bottom {
                display: grid;
                grid-template-columns: minmax(0, 30rem) minmax(0, 1fr);
                gap: 0.8rem;
                align-items: stretch;
                margin-top: 0.8rem;
            }

            .product-gallery {
                display: grid;
                gap: 0.8rem;
            }

            .product-gallery-main {
                min-height: 220px;
                cursor: zoom-in;
            }

            .product-gallery-main img {
                transition: transform 0.18s ease;
                transform-origin: center center;
            }

            .product-gallery-main.is-zoomed img {
                transform: scale(2.15);
            }

            .product-gallery-thumbs {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                width: 100%;
                height: 100%;
            }

            .product-gallery-thumb {
                width: 100%;
                height: 100%;
                min-height: 0;
                padding: 0;
                overflow: hidden;
                cursor: pointer;
                border-color: var(--line);
                background: rgba(255, 255, 255, 0.03);
            }

            .product-gallery-thumb img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .product-gallery-thumb.is-active {
                border-color: var(--accent);
                box-shadow: 0 0 0 1px rgba(91, 231, 255, 0.22);
            }

            .product-visual {
                position: relative;
                overflow: hidden;
                border-radius: 18px;
                border: 1px solid var(--line);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
                min-height: 240px;
            }

            .product-visual img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .product-meta h2 {
                margin: 0 0 0.5rem;
                font-size: 1.5rem;
            }

            .stock-pill {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                padding: 0.3rem 0.65rem;
                border: 1px solid var(--line);
                background: rgba(255, 255, 255, 0.03);
                font-size: 0.88rem;
                font-weight: 700;
            }

            .stock-pill.limited {
                color: #ffe28d;
                border-color: rgba(255, 226, 141, 0.32);
                background: rgba(255, 226, 141, 0.08);
            }

            .stock-pill.empty {
                color: var(--danger);
                border-color: rgba(255, 129, 152, 0.28);
                background: rgba(255, 129, 152, 0.08);
            }

            .list-stack {
                display: grid;
                gap: 1rem;
            }

            .receipt-header {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                align-items: start;
                flex-wrap: wrap;
            }

            .admin-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
                width: 100%;
            }

            .admin-toolbar-left,
            .admin-toolbar-right {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .admin-search {
                min-width: auto;
            }

            .admin-search input {
                width: 11.5rem;
            }

            .receipt-item {
                display: grid;
                grid-template-columns: 72px minmax(0, 1fr);
                gap: 0.85rem;
                align-items: center;
                padding: 0.85rem 0;
                border-top: 1px solid var(--line);
            }

            .receipt-item .product-visual {
                min-height: 72px;
            }

            .product-foot {
                display: flex;
                justify-content: space-between;
                align-items: end;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .inline-form {
                display: flex;
                align-items: center;
                gap: 0.6rem;
                flex-wrap: wrap;
            }

            .inline-form input {
                width: 5.5rem;
                border: 1px solid var(--line);
                border-radius: 10px;
                padding: 0.55rem 0.75rem;
                font: inherit;
                background: rgba(3, 8, 20, 0.8);
                color: var(--text);
            }

            .cart-layout {
                display: grid;
                grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.8fr);
                gap: 1rem;
                margin-top: 1.5rem;
                align-items: stretch;
            }

            .checkout-layout {
                display: grid;
                grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.8fr);
                gap: 1rem;
                margin-top: 1.5rem;
                align-items: stretch;
            }

            .cart-items {
                display: grid;
                gap: 1rem;
            }

            .cart-items-rest {
                grid-column: 1 / 2;
            }

            .checkout-form {
                display: grid;
                gap: 1rem;
                height: 100%;
                align-content: start;
            }

            .cart-item {
                display: grid;
                gap: 0.75rem;
                padding: 1.15rem;
            }

            .cart-item-main {
                display: grid;
                grid-template-columns: 92px minmax(0, 1fr);
                gap: 0.85rem;
                align-items: start;
            }

            .admin-product-main {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 450px;
                gap: 1rem;
                align-items: start;
            }

            .admin-product-visual {
                min-height: 220px;
            }

            .admin-product-images {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                align-items: start;
            }

            .cart-item-visual {
                min-height: 84px;
            }

            .cart-item h2,
            .summary-card h2,
            .empty-state h2 {
                margin: 0 0 0.5rem;
            }

            .cart-item-price {
                margin: 0;
                color: var(--text);
                font-size: 1.02rem;
                font-weight: 700;
                line-height: 1.35;
            }

            .cart-item-actions {
                display: flex;
                gap: 0.6rem;
                flex-wrap: wrap;
                align-items: center;
            }

            .account-card-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: end;
                gap: 1rem;
                flex-wrap: wrap;
                margin-top: 1rem;
            }

            .account-card-toolbar .actions {
                margin-top: 0;
            }

            .account-delete-modal {
                position: fixed;
                inset: 0;
                z-index: 50;
                display: grid;
                place-items: center;
                padding: 1.5rem;
            }

            .account-delete-modal[hidden] {
                display: none;
            }

            .account-delete-modal__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(2, 4, 13, 0.78);
                backdrop-filter: blur(12px);
            }

            .account-delete-modal__panel {
                position: relative;
                z-index: 1;
                width: min(100%, 30rem);
            }

            .account-delete-modal__actions {
                justify-content: flex-end;
            }

            .summary-card,
            .empty-state {
                display: grid;
                gap: 0.75rem;
            }

            .summary-card {
                padding: 1.15rem;
                align-content: start;
                height: 100%;
            }

            .summary-card .actions {
                justify-content: flex-end;
                flex-wrap: nowrap;
            }

            .summary-line {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                align-items: center;
                margin: 0;
                padding: 1rem 0;
                border-top: 1px solid var(--line);
                border-bottom: 1px solid var(--line);
            }

            .summary-line.plain-line {
                border-bottom: 0;
            }

            .summary-line.total-line {
                font-weight: 700;
            }

            .pagination-wrap {
                margin-top: 1.5rem;
            }

            .pagination-wrap nav {
                display: flex;
                justify-content: center;
            }

            .pagination-wrap svg {
                width: 1rem;
                height: 1rem;
            }

            .pagination-wrap > nav > div:first-child {
                display: none;
            }

            .pagination-wrap span,
            .pagination-wrap a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 2.5rem;
                min-height: 2.5rem;
                padding: 0.4rem 0.75rem;
                border: 1px solid var(--line);
                background: rgba(255, 255, 255, 0.03);
                color: var(--text);
                text-decoration: none;
            }

            .pagination-wrap span[aria-current="page"] > span,
            .pagination-wrap span[aria-disabled="true"] {
                background: var(--accent);
                border-color: var(--accent);
                color: white;
            }

            @media (max-width: 640px) {
                .shell { width: min(100% - 1rem, 1080px); }
                .card, .auth-card {
                    padding: 1.2rem;
                    border-radius: 16px;
                }

                .nav {
                    align-items: start;
                    flex-direction: column;
                }

                .product-grid {
                    grid-template-columns: 1fr;
                }

                .product-show-top,
                .product-show-bottom {
                    grid-template-columns: 1fr;
                }

                .cart-layout {
                    grid-template-columns: 1fr;
                }

                .cart-item-main {
                    grid-template-columns: 1fr;
                }

                .cart-items-rest {
                    grid-column: auto;
                }

                .checkout-layout {
                    grid-template-columns: 1fr;
                }
            }

            @media (min-width: 641px) and (max-width: 960px) {
                .product-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
        </style>
    </head>
    <body>
        <div class="shell">
            <nav class="nav">
                <div class="nav-group">
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
                        <a class="button secondary" href="{{ route('cart.index') }}">Cart ({{ $cartItemCount }})</a>
                    @endif
                </div>

                <div class="nav-links">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Logout</button>
                        </form>
                    @else
                        <a class="button secondary" href="{{ route('login') }}">Login</a>
                        <a class="button" href="{{ route('register') }}">Create Account</a>
                    @endauth
                </div>
            </nav>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </body>
</html>
