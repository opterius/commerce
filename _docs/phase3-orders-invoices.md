# Phase 3 — Orders & Invoicing

**Status:** Not started

Orders and invoicing form the core billing engine. This phase covers the full lifecycle: client places an order → invoice generated → payment collected (Stripe) → service activated.

---

## Scope

### Orders
- **Public order form** — product selection with configurable options, promo code field, domain input (if required)
- **Shopping cart** — session-based, supports multiple items
- **Order review** — summary with pricing breakdown, terms acceptance, captcha
- **Order approval** — auto-approve (default) or manual admin approval
- **Order statuses:** pending, active, fraud, cancelled
- **Welcome email** — sent on order completion with credentials (if provisioned)

### Invoicing
- **Auto-generate** recurring invoices before the due date (configurable days before)
- **Invoice statuses:** draft, unpaid, paid, overdue, cancelled, refunded
- **Invoice numbering** — configurable scheme (prefix, yearly reset, sequential)
- **Line items** — each invoice has line items (product, description, amount, tax)
- **PDF generation** — branded invoice PDF with company info, client info, line items, totals
- **Credit notes** — partial or full refund records linked to the original invoice

### Payments (Stripe)
- **Stripe integration** — credit card, SEPA (future: Apple Pay)
- **Payment methods** — clients save cards, admin can charge on file
- **Auto-charge** — attempt to charge saved payment method on invoice generation
- **Manual payments** — admin marks invoice as paid (bank transfer, cash, etc.)
- **Refunds** — full or partial, processed through Stripe or manual

### Account Credit
- **Credit balance** — clients can have a credit balance (top-up or from overpayment)
- **Apply to invoices** — credit is applied before charging the payment method

### Tax / VAT
- **Tax rules** — per-country VAT rates with EU reverse charge for B2B
- **Tax ID validation** — validate EU VAT numbers via VIES
- **Tax line items** — tax shown separately on invoices
- **Tax-exempt** — flag on client record for tax-exempt entities

### Late Fees / Overdue Handling
- **Grace period** — configurable days after due date before suspension
- **Late fees** — optional fixed or percentage late fee added to overdue invoices
- **Automatic reminders** — email reminders before and after due date (configurable schedule)

---

## Database Tables (Planned)

| Table | Purpose |
|-------|---------|
| `orders` | Order records (client_id, status, promo_code_id, total, currency_code, notes) |
| `order_items` | Line items per order (product_id, billing_cycle, qty, price, setup_fee, domain, config_options JSON) |
| `services` | Active services / hosting accounts (client_id, product_id, order_id, server_id, status, domain, next_due_date, billing_cycle, amount) |
| `invoices` | Invoice records (client_id, invoice_number, status, due_date, paid_date, subtotal, tax, total, currency_code, notes) |
| `invoice_items` | Line items (invoice_id, description, amount, tax_amount, service_id) |
| `payments` | Payment records (invoice_id, gateway, transaction_id, amount, currency_code, status, method) |
| `credit_notes` | Refund/credit records linked to invoices |
| `client_credits` | Credit balance transactions (client_id, amount, description, invoice_id) |
| `tax_rules` | Per-country tax rates (country_code, rate, name, applies_to) |
| `payment_methods` | Saved Stripe payment methods (client_id, stripe_pm_id, brand, last4, expiry, is_default) |

---

## Key Technical Decisions

### Stripe Integration
- Use `stripe/stripe-php` SDK
- Stripe Customer created per Commerce client
- PaymentIntent for one-time charges, Stripe doesn't manage recurring (Commerce handles the schedule)
- Webhook endpoint for payment confirmations, disputes, refunds

### Invoice Numbering
- Configurable via settings: prefix (e.g., "INV-"), zero-padding width, yearly reset toggle
- Example: `INV-2026-00042`

### Pro-Rata Billing
- Mid-cycle upgrades/downgrades calculate the remaining days on the current cycle
- Credit the unused portion of the old plan, charge the remaining portion of the new plan

### PDF Generation
- Use `barryvdh/laravel-dompdf` or `spatie/laravel-pdf`
- Template stored as a Blade view, customizable via branding settings

---

## Admin Pages

- **Orders** — list with filters (status, client, date range), order detail view
- **Invoices** — list with filters, invoice detail with payment history, manual payment form
- **Payments** — transaction log with gateway details
- **Tax Rules** — CRUD for per-country rates
- **Invoice Settings** — numbering scheme, due date offset, late fee config, reminder schedule

## Client Portal Pages

- **My Invoices** — list of invoices with status badges, pay button for unpaid
- **Payment** — Stripe checkout form (Elements), saved payment methods
- **Billing History** — payment receipts
