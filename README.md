# Stripe PHP Store

This project includes:

- 4 fixed products in BRL: `R$ 80,00`, `R$ 60,00`, `R$ 40,00`, `R$ 20,00`
- account registration
- account login
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

