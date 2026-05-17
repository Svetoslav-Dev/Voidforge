# User Credentials

These accounts exist only when demo mode is enabled.

Enable `DEMO_MODE=true` in both `.env` and `src/.env`, then reseed with:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Admin user

- Name: `Demo Admin`
- Email: `demo-admin@example.test`
- Password: `DemoPass123!`

## Regular user with receipts

- Name: `Cookie`
- Email: `demo-user@example.test`
- Password: `DemoPass123!`

This regular user is seeded with demo purchase history / receipts.

## Discount codes
- WELCOME10 does 10% off
- VOID5 does 5.00 EUR off

### Stripe test cards

- Visa: `4242 4242 4242 4242`
  - Expiry: `12/34`
  - CVC: `123`
- Mastercard: `5555 5555 5555 4444`
  - Expiry: `12/34`
  - CVC: `123`
