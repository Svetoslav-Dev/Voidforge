# Voidforge

Voidforge is a Laravel e-commerce storefront for selling shirts, with authentication, catalog browsing, cart and shipping flow, hosted Stripe and PayPal payments, saved addresses and cards, receipts and PDF invoices, queued order emails, and an admin panel.

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
- product catalog with category filtering and AJAX cart actions
- cart, shipping, payment, and completed-order flow
- hosted Stripe and PayPal checkout
- saved shipping addresses and saved Stripe cards
- customer receipt history and PDF invoice download
- queued order-completion emails with retry support
- admin management for shirts, categories, users, orders, and discount codes
- local self-hosting with optional Cloudflare Tunnel exposure

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
docker-compose up --build
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
- seeds demo data
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

### 6. Rebuild frontend assets after JS or CSS changes

```bash
docker compose exec app npm run build
```

## Demo Accounts

The seeded local setup creates:

- Admin: `demo-admin@example.test` / `DemoPass123!`
- Customer: `demo-user@example.test` / `DemoPass123!`

Both users have seeded receipts and default shipping addresses.

Seeded demo data also includes:

- completed and pending orders
- default shipping addresses
- catalog categories and shirts
- default discount codes

## Payment Configuration

Stripe and PayPal use environment variables only. Do not hardcode secrets.

Relevant variables are in:

- `.env.example`
- `src/.env.example`

Additional notes:

- payment test credentials: [PaymentCredentials.md](/home/thinkpadl14/Projects/Voidforge/PaymentCredentials.md)
- user credentials: [UserCredentials.md](/home/thinkpadl14/Projects/Voidforge/UserCredentials.md)
- improvement notes: [Improvements](/home/thinkpadl14/Projects/Voidforge/Improvements)

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

Docker now passes these mail variables through from the project root `.env`, so you can switch the whole stack to Brevo without touching PHP code.

Order completion emails are queued after a paid order and tracked on the order as `pending`, `sent`, or `failed`.

For a real public store, prefer a sender on your own domain such as `orders@voidforgestore.com` instead of a temporary mailbox.

To retry failed or stuck order emails manually:

```bash
docker compose exec app php artisan orders:retry-completed-emails
```

## Production Notes

To run the site publicly on `voidforgestore.com` from your own computer, the repo includes Cloudflare Tunnel support. The current tunnel setup uses Cloudflare public HTTPS and forwards traffic internally to `http://app:8000`, so no router port forwarding is required.

See:

- [Improvements/CloudflareTunnel.md](/home/thinkpadl14/Projects/Voidforge/Improvements/CloudflareTunnel.md)
- [Improvements/Certificates.md](/home/thinkpadl14/Projects/Voidforge/Improvements/Certificates.md)
- [Improvements/ProductionChecklist.md](/home/thinkpadl14/Projects/Voidforge/Improvements/ProductionChecklist.md)
