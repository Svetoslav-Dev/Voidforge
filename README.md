# VoidForgeStore

VoidForgeStore is a Laravel e-commerce storefront for selling shirts, with authentication, catalog browsing, cart and shipping flow, hosted Stripe and PayPal payments, saved addresses and cards, receipts and PDF invoices, queued order emails, legal policy pages, cookie preferences, and an admin panel.

## Stack

- Laravel
- MariaDB
- Docker
- TypeScript
- Brevo SMTP
- Stripe
- PayPal
- Cloudflare Tunnel

## What The App Does

- customer authentication and account management
- product catalog with category filtering and cart actions
- cart, shipping, payment, and completed-order flow
- hosted Stripe and PayPal checkout
- saved shipping addresses and saved Stripe cards
- customer receipt history and PDF invoice download
- queued order-completion emails with retry support
- legal policy pages, cookie preferences modal, and a public privacy/support request form
- admin management for shirts, categories, users, orders, and discount codes
- local self-hosting with Cloudflare Tunnel for public HTTPS exposure

## Run Locally

### Requirements

- Docker
- Docker Compose

### 1. Create env files

At the project root:

```bash
cp .env.example .env
```

Inside the Laravel app:

```bash
cp src/.env.example src/.env
```

### 2. Build and start containers

```bash
docker compose up --build
```

The app container serves:

- HTTP: `http://127.0.0.1:8000`
- HTTPS: `https://127.0.0.1:8443`

HTTPS uses a local self-signed certificate by default.

### 3. Install app dependencies and bootstrap the project

Run inside the app container:

```bash
docker compose exec app composer setup
```

This does:

- installs PHP dependencies
- creates `src/.env` if missing
- generates the Laravel app key
- runs migrations
- seeds the base catalog and defaults
- installs frontend dependencies
- builds frontend assets

### 4. Run tests

```bash
docker compose exec -T app php artisan test
```

### 5. Run queued jobs locally

Order confirmation emails are queued. Keep a worker running while testing payments or retrying email delivery:

```bash
docker compose exec app php artisan queue:work --tries=5
```

### 6. Run the scheduler

Cart reminders and the sitemap are scheduled daily. Keep the Laravel scheduler running in environments where you want these to fire automatically:

```bash
docker compose exec app php artisan schedule:work
```

### 7. Rebuild frontend assets after JS or CSS changes

```bash
docker compose exec app npm run build
```

## Demo Mode

Shared demo accounts are now opt-in. The default setup does not create the public admin/customer users.

To enable the throwaway public demo accounts, set `DEMO_MODE=true` in both `.env` and `src/.env`, then reseed:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

With demo mode enabled, the seeded setup creates:

- Admin: `demo-admin@example.test` / `DemoPass123!`
- Customer: `demo-user@example.test` / `DemoPass123!`

Both users have seeded receipts and default shipping addresses.

Demo mode also includes:

- completed and pending orders
- default shipping addresses
- catalog categories and shirts
- default discount codes

See [TestCredentials/UserCredentials.md](TestCredentials/UserCredentials.md) for the shared demo credentials.

## Payment Configuration

Stripe and PayPal use environment variables only. Do not hardcode secrets.

Relevant variables are in `.env.example` and `src/.env.example`.

Stripe hosted Checkout defers wallet availability to Stripe Dashboard settings for hosted payment sessions. That allows wallet methods such as Google Pay and Apple Pay to appear when Stripe says the current device, browser, HTTPS domain, and customer wallet state support them.

Wallet testing is primarily manual:

- test Google Pay in a supported Chrome environment with Google Pay already configured
- test Apple Pay in Safari on a supported Apple device with Apple Pay configured
- use the live HTTPS domain when testing wallets, not plain local HTTP

## Cookie Consent

Cookie preferences are handled client-side with a centered modal popup. Choosing an option dismisses the modal immediately without a page refresh. The choice is persisted to `localStorage` and a first-party cookie. A background AJAX call syncs the preference to the server session so the modal stays dismissed across page loads.

The `[hidden]` CSS reset is applied globally so the `hidden` attribute always works regardless of display rules on the element.

## Email Configuration

Transactional mail is configured through SMTP environment variables only. For Brevo, use:

- `MAIL_MAILER=smtp`
- `MAIL_SCHEME=null`
- `MAIL_HOST=smtp-relay.brevo.com`
- `MAIL_PORT=587`
- `MAIL_USERNAME=your-brevo-smtp-login`
- `MAIL_PASSWORD=your-brevo-smtp-key`
- `MAIL_EHLO_DOMAIN=voidforgestore.com`
- `MAIL_FROM_ADDRESS=orders@voidforgestore.com`
- `MAIL_FROM_NAME=Voidforge`

Docker passes these mail variables through from the project root `.env`.

Order completion emails are queued after a paid order and tracked on the order as `pending`, `sent`, or `failed`.

To retry failed or stuck order emails manually:

```bash
docker compose exec app php artisan orders:retry-completed-emails
```

To queue due cart reminders manually:

```bash
docker compose exec app php artisan cart:send-reminders
```

Public legal/privacy contact requests are also queued through the same Laravel mail setup.

## Sitemap

A sitemap is generated at `public/sitemap.xml` by the `sitemap:generate` artisan command. It includes all static public pages and every active product page. The sitemap regenerates daily by the Laravel scheduler at Bulgarian time (Europe/Sofia).

To generate it manually:

```bash
docker compose exec app php artisan sitemap:generate
```

`robots.txt` points search engines to `https://voidforgestore.com/sitemap.xml` automatically.

## Legal Configuration

The storefront reads trader and policy-facing contact details from environment variables:

- `LEGAL_TRADER_NAME`
- `LEGAL_TRADER_ADDRESS`
- `LEGAL_TRADER_REGISTRATION_NUMBER`
- `LEGAL_TRADER_VAT_NUMBER`
- `LEGAL_SUPPORT_EMAIL`
- `LEGAL_PRIVACY_EMAIL`
- `LEGAL_COMPLAINTS_EMAIL`
- `LEGAL_SUPPORT_PHONE`
- `LEGAL_RETURNS_WINDOW_DAYS`
- `LEGAL_SHIPPING_REGIONS`
- `LEGAL_DISPATCH_WINDOW`
- `LEGAL_REFUND_WINDOW`

These values feed the footer, legal pages, and the public request page. Update them before launch so the published legal details match the real business.

## Legal And Compliance Features

- legal pages for privacy, terms, returns, shipping, cookies, and trader information
- footer links to those pages on every screen
- pre-payment policy links on the shipping step
- a cookie-preferences modal and footer shortcut
- a public request form for privacy, returns, order-support, and general contact requests

The legal text is placeholder-oriented and should be replaced with real business and policy details before going live.

## Production Setup

The site runs publicly at `https://voidforgestore.com` through a Cloudflare Tunnel running as a systemd service on the host machine. No router port forwarding is needed.

```
Browser ──HTTPS──▶ Cloudflare ──HTTPS──▶ localhost:8443 (self-signed cert, noTLSVerify)
```

Cloudflare manages and auto-renews the public certificate. The internal leg uses a self-signed certificate which the tunnel skips validating.

`AppServiceProvider` calls `URL::forceRootUrl` and `URL::forceScheme` unconditionally, deriving the scheme from `APP_URL`. This prevents the internal port 8443 from leaking into generated asset URLs and redirects when the request arrives through the tunnel.

See:

- [GettingStarted/CloudflareSetup.md](GettingStarted/CloudflareSetup.md)
- [GettingStarted/CloudflareTunnel.md](GettingStarted/CloudflareTunnel.md)
- [GettingStarted/Certificates.md](GettingStarted/Certificates.md)
- [GettingStarted/ProductionChecklist.md](GettingStarted/ProductionChecklist.md)
