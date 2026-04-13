# Phase 1 — Foundation

**Status:** Complete (2026-04-12)

The foundation phase builds every building block that the rest of Commerce depends on: dual auth, admin layout, client portal, client management, settings, and i18n.

---

## Architecture Decisions

### Dual Auth Guards

Commerce uses two completely separate authenticatable models instead of a single users table:

- **`staff` guard** — admin users who manage the billing platform (table: `staff`)
- **`client` guard** — client accounts who log in to the client portal (table: `clients`)

Each guard has its own login page, password reset flow, and session. Staff can impersonate clients via "Login as Client" (stores `impersonating_staff_id` in session).

Why not one table? The build plan requires multi-contact per client account with separate logins, client groups/tags/notes, and staff role-based permissions. Two tables is cleaner and matches how WHMCS/Blesta work.

### Money Storage

All monetary amounts are stored as `BIGINT` (unsigned) representing the minor unit (cents for USD/EUR). Every table that stores money also stores a `currency_code CHAR(3)` column.

### Settings: Key-Value with Cache

The `settings` table uses a `group`/`key`/`value` pattern with static `Setting::get()`, `Setting::set()`, `Setting::getGroup()` methods backed by `Cache::rememberForever()`. Matches the Panel's pattern exactly.

### Custom Fields: EAV

Admin-defined custom fields on client records use `custom_fields` (field definitions) + `custom_field_values` (per-entity values) tables. The `entity_type` column allows reuse for products, orders, etc. in future phases.

---

## Database Schema

| Table | Purpose |
|-------|---------|
| `staff` | Admin users (name, email, password, role, 2FA, last_login) |
| `clients` | Client accounts (name, company, address, tax_id, currency, group, status, 2FA) |
| `client_contacts` | Sub-users per client (separate login, role: billing/technical/admin) |
| `client_groups` | Grouping clients (name, color, description) |
| `client_tags` | Tags for clients (name, color) |
| `client_tag` | Pivot: client ↔ tag |
| `client_notes` | Staff notes on client records (body, is_sticky, staff_id) |
| `custom_fields` | Field definitions (entity_type, name, field_type, options, required) |
| `custom_field_values` | Per-entity values (custom_field_id, entity_type, entity_id, value) |
| `currencies` | Supported currencies (code, name, symbol, prefix/suffix, decimal_places, exchange_rate, is_default) |
| `settings` | Key-value config (group, key, value) |
| `activity_logs` | Audit trail (staff_id, client_id, action, entity_type/id, metadata, ip) |
| `sessions` | Laravel sessions |
| `password_reset_tokens` | Staff password resets |
| `client_password_reset_tokens` | Client password resets |
| `cache` | Laravel cache (default migration) |
| `jobs` | Laravel queue jobs (default migration) |

---

## Staff Roles

| Role | Permissions |
|------|-------------|
| `super_admin` | Everything |
| `admin` | Everything except system-level changes |
| `support` | Clients, tickets, read-only on billing |
| `billing` | Clients, invoices, payments, read-only on support |

Role checking via `Staff::isSuper()`, `isAdmin()`, `isSupportAgent()`, `isBillingAgent()`.

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── StaffLoginController.php
│   │   │   ├── ClientLoginController.php
│   │   │   ├── StaffForgotPasswordController.php
│   │   │   ├── StaffResetPasswordController.php
│   │   │   ├── ClientForgotPasswordController.php
│   │   │   └── ClientResetPasswordController.php
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ClientController.php
│   │   │   ├── ClientContactController.php
│   │   │   ├── ClientNoteController.php
│   │   │   └── SettingsController.php
│   │   └── Client/
│   │       ├── DashboardController.php
│   │       └── ProfileController.php
│   └── Middleware/
│       ├── StaffMiddleware.php
│       ├── ClientMiddleware.php
│       └── SetLocale.php
├── Models/
│   ├── Staff.php
│   ├── Client.php
│   ├── ClientContact.php
│   ├── ClientGroup.php
│   ├── ClientTag.php
│   ├── ClientNote.php
│   ├── CustomField.php
│   ├── CustomFieldValue.php
│   ├── Currency.php
│   ├── Setting.php
│   └── ActivityLog.php
├── Services/
│   └── ActivityLogger.php
└── View/Components/
    ├── AdminLayout.php
    ├── ClientLayout.php
    └── GuestLayout.php

resources/
├── views/
│   ├── layouts/ (admin, client, guest)
│   ├── partials/ (admin-sidebar, admin-topbar, client-sidebar, client-topbar, flash-messages)
│   ├── components/ (15 reusable Blade components)
│   ├── auth/ (staff-login, client-login, forgot-password, reset-password)
│   ├── admin/
│   │   ├── dashboard.blade.php
│   │   ├── clients/ (index, create, show, edit)
│   │   └── settings/ (index, _company, _branding, _currencies)
│   └── client/ (dashboard, profile)
├── lang/en/ (common, auth, dashboard, clients, settings, navigation)
└── css/app.css, js/app.js
```

---

## Routes

### Staff Auth (no middleware)
- `GET/POST /admin/login` — staff login
- `POST /admin/logout` — staff logout
- `GET/POST /admin/forgot-password` — password reset request
- `GET/POST /admin/reset-password/{token}` — password reset

### Client Auth (no middleware)
- Same pattern at `/client/login`, `/client/logout`, etc.

### Admin Panel (`auth:staff` + `staff` middleware)
- `GET /admin/dashboard` — dashboard
- Resource: `/admin/clients` (index, create, store, show, edit, update, destroy)
- `POST /admin/clients/{client}/notes` — add note
- `POST /admin/clients/{client}/contacts` — add contact
- `POST /admin/login-as-client/{client}` — impersonate
- `GET /admin/settings/{category?}` — settings (company, branding, currencies)

### Client Portal (`auth:client` + `client` middleware)
- `GET /client/dashboard` — client dashboard
- `GET/PUT /client/profile` — edit profile
- `POST /client/return-to-admin` — end impersonation

---

## Frontend Stack

- **Tailwind CSS v4** — CSS-first config, Inter font, `@tailwindcss/forms`, `@tailwindcss/typography`
- **Alpine.js** — tabs, modals, dropdowns, sidebar toggle
- **Vite** — asset bundling
- **No SPA** — all Blade, no Livewire, no React/Vue

---

## Database Seeder

Default seed creates:
- Super admin: `admin@example.com` / `password`
- Currencies: USD (default), EUR
- Company settings with sensible defaults
- Branding: "Client Portal", indigo primary color
