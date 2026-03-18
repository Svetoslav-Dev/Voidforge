# Project: Voidforge

## Goal
Build a secure Laravel e-commerce site for selling t-shirts.

## Stack
- Laravel
- MariaDB
- Docker

## Core features
- Authentication
- Product catalog
- Cart
- Checkout
- Admin panel
- Stripe + PayPal payments

## Admin rules
- Admin can add/edit/delete products
- Use Laravel soft deletes (deleted_at) for products
- Admin can view orders

## Payment rules
- Use Stripe and PayPal
- NEVER store card data
- Use hosted checkout or official SDKs
- Use webhooks to confirm payments
- Store only: provider, transaction_id, amount, status, order_id

## Security rules
- Validate all input
- Use Laravel auth and CSRF protection
- Hash passwords using Laravel defaults (never encrypt passwords)
- Use env variables for secrets
- Never commit secrets to version control
- Do not trust client-side data

## Testing rules
- Add unit tests for business logic
- Tests must be runnable with: php artisan test
- Keep test setup compatible with Jenkins
- Prefer simple CI-friendly configuration
- Use SQLite in-memory database for tests when possible

## Database
Tables:
- users
- products
- categories
- orders
- order_items
- payments

## Docker
Provide:
- app container
- MariaDB container
- environment-based configuration

Project must run with:
docker-compose up --build

## Workflow
Build in steps:
1. Docker setup
2. Laravel setup
3. Database + migrations
4. Auth
5. Products
6. Cart
7. Checkout
8. Stripe
9. PayPal
10. Admin panel
11. Tests

## Instructions
- Keep it simple
- Do not over-engineer
- Explain major changes
- Ask before deleting files
- Keep business logic out of controllers when possible so it can be unit tested

## Secrets and environment
- Use environment variables for all sensitive configuration
- Never hardcode API keys, passwords, or credentials in code
- Never commit secrets to version control
- Use only test/sandbox credentials during local development
- Provide a .env.example file with placeholders for required variables
- Read configuration from environment variables only
- Do not log or expose secrets in application output