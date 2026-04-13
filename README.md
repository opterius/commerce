# Opterius Commerce

Billing and client management for hosting companies. A modern alternative to WHMCS and Blesta — built for use with [Opterius Panel](https://github.com/opterius/panel).

## Features

- **Client portal** — clients log in to manage services, pay invoices, and open support tickets
- **Admin panel** — full billing management for staff with role-based access
- **Products & pricing** — multi-currency pricing matrix, billing cycles, configurable options, promo codes
- **Orders & invoicing** — automated invoice generation, Stripe payments, VAT/tax rules, PDF invoices
- **Provisioning** — automatic hosting account creation/suspension/termination via the Opterius Panel API
- **Support tickets** — built-in help desk with departments, priorities, canned responses, and email piping
- **Dual auth** — separate staff and client authentication guards, client impersonation

## Requirements

- PHP 8.3+
- Laravel 13
- MySQL 8.0+
- Node.js 20+ (for asset builds)

## Installation

```bash
git clone https://github.com/opterius/commerce
cd commerce
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Configure your `.env` with database credentials, mail settings, and Stripe keys.

Default admin login after seeding: `admin@example.com` / `password`

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3
- **Frontend:** Blade, Tailwind CSS v4, Alpine.js
- **Queue:** Laravel queues (database driver, Redis recommended for production)
- **Payments:** Stripe (via `stripe/stripe-php`)

## Project Status

| Phase | Description | Status |
|-------|-------------|--------|
| 1 | Foundation — auth, clients, settings, i18n | Complete |
| 2 | Products & pricing — catalog, configurable options, promo codes | Complete |
| 3 | Orders & invoicing — Stripe, VAT, PDF | Planned |
| 4 | Provisioning — Opterius Panel API integration | Planned |
| 5 | Support tickets — help desk, email piping | Planned |

## License

GNU Affero General Public License v3.0 — see [LICENSE](LICENSE).

AGPL-3.0 means you can use, modify, and self-host freely. If you offer Commerce as a hosted service to others, you must publish your source changes under the same license.
