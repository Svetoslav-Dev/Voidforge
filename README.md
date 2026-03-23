# Voidforge

Voidforge is a Laravel e-commerce project for selling shirts, with authentication, cart, shipping, checkout, hosted Stripe and PayPal payments, receipts, and an admin panel.

## Stack

- Laravel
- MariaDB
- Docker
- TypeScript
- Stripe
- PayPal

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

### 5. Rebuild frontend assets after JS or CSS changes

```bash
docker compose exec app npm run build
```

## Demo Accounts

The seeded local setup creates:

- Admin: `demo-admin@example.test` / `DemoPass123!`
- Customer: `demo-user@example.test` / `DemoPass123!`

Both users have seeded receipts and default shipping addresses.

## Payment Configuration

Stripe and PayPal use environment variables only. Do not hardcode secrets.

Relevant variables are in:

- `.env.example`
- `src/.env.example`

Additional notes:

- payment test credentials: [PaymentCredentials.md](/home/thinkpadl14/Projects/Voidforge/PaymentCredentials.md)
- user credentials: [UserCredentials.md](/home/thinkpadl14/Projects/Voidforge/UserCredentials.md)
- improvement notes: [Improvements](/home/thinkpadl14/Projects/Voidforge/Improvements)
