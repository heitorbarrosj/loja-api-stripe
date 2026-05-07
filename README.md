# Loja API Stripe

Loja virtual usando PHP com integracao da API de pagamento Stripe feito em vibe code.

This project includes:

- 50 products in BRL with prices from `R$ 10,00` to `R$ 100,00`
- product search and price sorting
- account registration
- account login
- customer address editing
- Stripe Checkout payment flow
- MySQL order storage
- a `My Customers` page where the logged-in customer can see their orders

## Configure

Edit `.env` and set:

- `STRIPE_SECRET_KEY`
- `STRIPE_PUBLISHABLE_KEY`
- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

## Database

Import `database.sql` into MySQL before opening the project.

