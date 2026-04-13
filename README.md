# Opterius Commerce

Billing and client management for hosting companies. A modern, standalone alternative to WHMCS and Blesta — runs independently and integrates with [Opterius Panel](https://github.com/opterius/panel) for server provisioning.

## Features

### Client Portal
- Client registration and login with dedicated auth guard
- Dashboard with active services, recent invoices, and open tickets
- Service management — view details, request upgrades/cancellations
- Invoice list, payment via Stripe, downloadable PDF invoices
- Credit balance — apply credits to invoices
- Support tickets — open tickets, reply with attachments, view history
- Domain management — search, register, renew, transfer, manage nameservers, EPP codes, WHOIS privacy, transfer lock, auto-renew

### Admin Panel
- Full billing management for staff with role-based access (Admin / Support roles)
- Client impersonation — log in as any client for troubleshooting
- Client management — create, edit, suspend/unsuspend, view activity log
- Service management — create, update, suspend, terminate, sync with provisioning
- Order processing — approve, provision, cancel, apply promo codes
- Invoice management — create, edit, mark paid, void, send reminders, PDF generation
- Payment recording — manual payments, credit adjustments
- Support ticket queue — assign departments, set priorities, canned responses, internal notes
- Domain management — sync from registrar, update nameservers, toggle privacy/lock, view EPP codes
- TLD manager — configure TLDs with register/renew/transfer prices, min/max years, WHOIS privacy availability
- Activity log — all staff and client actions recorded with timestamps

### Products & Pricing
- Product catalog with categories, billing cycles (monthly, quarterly, semi-annual, annual, biennial, triennial)
- Multi-currency pricing matrix — per-currency prices per billing cycle
- Configurable options — checkboxes, dropdowns, quantities, radio buttons with cycle-aware pricing
- Promo codes — percentage and fixed discounts, usage limits, expiry dates, per-product restrictions
- Free trial periods, setup fees

### Orders & Invoicing
- Automated invoice generation on order and on renewal due dates
- Stripe payment gateway — card payments via Stripe Checkout
- VAT/tax rules — per-country rates, tax-inclusive pricing, EU VAT handling
- PDF invoice generation with company branding
- Invoice sequences with configurable prefix and yearly reset
- Grace period and auto-close settings
- Credit system — client credit balance, credit applications to invoices

### Provisioning
- Automatic hosting account creation, suspension, unsuspension, and termination via Opterius Panel API
- Queue-based provisioning with retry logic (3 attempts, configurable backoff)
- Sync command to reconcile service status against the panel

### Support Tickets
- Multi-department ticket routing
- Priority levels (Low, Medium, High, Urgent)
- File attachments with configurable size and type limits
- Canned responses for common replies
- Internal notes visible only to staff
- Email piping — inbound email creates/updates tickets automatically
- Auto-close after configurable idle days

### Domain Registration
Full domain lifecycle management with a unified registrar interface. All registrars implement the same 11-method contract, making them interchangeable from a single settings toggle.

**Supported registrars:**

| Registrar | Protocol | Auth | Since |
|---|---|---|---|
| ResellerClub (LogicBoxes) | HTTP JSON | auth-userid + api-key | v1.0.0 |
| Enom (Tucows) | HTTPS GET / XML | uid + pw | v1.1.0 |
| OpenSRS (Tucows) | HTTPS POST / XML envelope | X-Username + HMAC-MD5 | v1.2.0 |
| Namecheap | HTTPS GET / XML | ApiUser + ApiKey + ClientIp | v1.3.0 |
| CentralNic Reseller (Hexonet) | HTTPS POST / plain-text | s_login + s_pw | v1.4.0 |

**Domain operations:**
- Availability search — single and bulk TLD check
- Registration — full registrant/admin/tech/billing contact submission
- Renewal — manual and automatic (invoice-driven)
- Transfer — EPP/auth code submission
- Nameserver management — update up to 4 nameservers
- WHOIS privacy — enable/disable per domain (WhoisGuard, WHOIS Trustee, etc.)
- Transfer lock — lock and unlock
- EPP code retrieval — fetched on demand, never stored
- Domain sync — pull live status, expiry date, and nameservers from registrar
- Auto-renew — generates renewal invoice before expiry, dispatches renewal job on payment

**Queue jobs:** RegisterDomainJob, RenewDomainJob, TransferDomainJob, SyncDomainJob — all queue-backed with retry logic.

**Scheduled commands:**
- `commerce:check-expiring-domains` — daily at 04:00, generates renewal invoices for domains due within the billing cycle
- `commerce:sync-domain-statuses` — daily at 05:00, syncs status and expiry from registrar for all active domains

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
- **Payments:** Stripe (via `stripe/stripe-php`)
- **Queue:** Laravel queues (database driver, Redis recommended for production)
- **PDF:** Laravel DomPDF
- **Money:** All amounts stored as integers (cents) — no floating-point currency math

## Project Status

| Phase | Description | Status |
|---|---|---|
| 1 | Foundation — auth, clients, staff roles, settings, i18n, activity log | Complete |
| 2 | Products & pricing — catalog, configurable options, promo codes, multi-currency | Complete |
| 3 | Orders & invoicing — Stripe, VAT/tax, PDF invoices, credit system | Complete |
| 4 | Provisioning — Opterius Panel API, queue-based with retry | Complete |
| 5 | Support tickets — help desk, email piping, canned responses, attachments | Complete |
| 6 | Domain registration — multi-registrar, full domain lifecycle, TLD manager | Complete |

## Releases

| Version | Highlight |
|---|---|
| v1.0.0 | First stable — all 6 phases complete, ResellerClub registrar |
| v1.1.0 | Enom (Tucows) registrar |
| v1.2.0 | OpenSRS (Tucows) registrar |
| v1.3.0 | Namecheap registrar |
| v1.4.0 | CentralNic Reseller (Hexonet) registrar |

## License

GNU Affero General Public License v3.0 — see [LICENSE](LICENSE).

AGPL-3.0 means you can use, modify, and self-host freely. If you offer Commerce as a hosted service to others, you must publish your source changes under the same license.
