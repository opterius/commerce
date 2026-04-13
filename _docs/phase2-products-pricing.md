# Phase 2 — Products & Pricing

**Status:** Complete (2026-04-13)

Products & Pricing builds the catalog system — everything that orders and invoicing depend on.

---

## What Was Built

### Product Groups
Categories to organize products on the order form. Each group has a name, slug, description, sort order, and visibility toggle.

### Products
The core sellable entity. Each product belongs to a group and has:
- **Type:** `hosting` (provisioned via a module) or `other` (manual fulfillment)
- **Status:** `active` (visible + orderable), `hidden` (orderable via direct link only), `retired` (not orderable)
- **Provisioning module:** `opterius_panel` (auto-create on Panel) or null (manual)
- **Stock control:** optional, limits orders when qty_in_stock reaches 0
- **Domain requirement:** flag for products that need a domain at checkout

### Pricing Matrix
Each product has pricing defined per **billing cycle** per **currency**:

| Cycle | Key |
|-------|-----|
| Monthly | `monthly` |
| Quarterly | `quarterly` |
| Semi-Annual | `semi_annual` |
| Annual | `annual` |
| Biennial | `biennial` |
| One-Time | `one_time` |

Each pricing row stores `price` and `setup_fee` in **minor units (cents)**. Leave blank to disable a cycle for a product. The admin UI accepts human-readable values (e.g., `9.99`) and converts to/from cents automatically.

### Configurable Options
Add-ons that customers select during checkout (e.g., extra disk space, RAM, backup frequency):

- **Option Groups** — containers linked to one or more products
- **Options** — individual choices within a group (e.g., "Disk Space")
- **Option Values** — the selectable values (e.g., "10 GB", "20 GB", "50 GB")
- **Input types:** dropdown, radio buttons, checkbox, quantity
- **Pricing:** each value can have per-cycle per-currency pricing (stored in `configurable_option_pricing`)

### Promo Codes / Coupons
Discount codes for customers:

- **Type:** `percent` (e.g., 20% off) or `fixed` (e.g., $5.00 off)
- **Recurring:** applies every billing cycle, or first payment only
- **Scope:** all products, or specific products (via pivot table)
- **Limits:** max uses (null = unlimited), date range (start_date, end_date)
- **Validation:** `PromoCode::isValid()` checks active, not exhausted, within date range

Values stored in minor units: 20% = `2000`, $5.00 = `500`.

---

## Database Schema

| Table | Purpose |
|-------|---------|
| `product_groups` | Product categories (name, slug, description, sort_order, is_visible) |
| `products` | Products (name, slug, group_id, type, status, module, stock, domain, sort_order) |
| `product_pricing` | Per-cycle per-currency pricing (product_id, currency_code, billing_cycle, price, setup_fee). Unique on [product_id, currency_code, billing_cycle] |
| `configurable_option_groups` | Option group containers (name, description) |
| `product_configurable_group` | Pivot: product ↔ option group |
| `configurable_options` | Options within a group (name, option_type, sort_order) |
| `configurable_option_values` | Values within an option (label, sort_order) |
| `configurable_option_pricing` | Per-value per-cycle per-currency pricing |
| `promo_codes` | Discount codes (code, type, value, recurring, applies_to, max_uses, dates) |
| `promo_code_product` | Pivot: promo code ↔ specific products |

---

## File Structure

```
app/
├── Http/Controllers/Admin/
│   ├── ProductGroupController.php    # CRUD for groups
│   ├── ProductController.php         # CRUD for products + pricing save
│   ├── ConfigurableOptionController.php  # Groups, options, values
│   └── PromoCodeController.php       # CRUD for promo codes
└── Models/
    ├── ProductGroup.php
    ├── Product.php
    ├── ProductPricing.php
    ├── ConfigurableOptionGroup.php
    ├── ConfigurableOption.php
    ├── ConfigurableOptionValue.php
    ├── ConfigurableOptionPricing.php
    └── PromoCode.php

resources/views/admin/products/
├── index.blade.php          # Product list with filters
├── create.blade.php         # Tabbed: Details + Pricing matrix
├── show.blade.php           # Tabbed: Details + Pricing (read-only) + Options
├── edit.blade.php           # Tabbed: same as create, pre-filled
├── groups/
│   ├── index.blade.php      # Group list
│   ├── create.blade.php
│   └── edit.blade.php
├── options/
│   ├── index.blade.php      # Option groups list
│   ├── create.blade.php
│   └── show.blade.php       # Group detail + inline option/value management
└── promos/
    ├── index.blade.php      # Promo codes list
    ├── create.blade.php     # Alpine.js conditional product selection
    └── edit.blade.php
```

---

## Routes

All under `Route::prefix('admin')->middleware(['auth:staff', 'staff'])->name('admin.')`:

```
Resource: /admin/products              (index, create, store, show, edit, update, destroy)
Resource: /admin/product-groups        (index, create, store, edit, update, destroy)
Resource: /admin/configurable-options  (index, create, store, show, update, destroy)
POST     /admin/configurable-options/{group}/options           — add option
DELETE   /admin/configurable-options/{group}/options/{option}  — remove option
POST     /admin/configurable-options/options/{option}/values   — add value
DELETE   /admin/configurable-options/options/{option}/values/{value} — remove value
Resource: /admin/promo-codes           (index, create, store, edit, update, destroy)
```

---

## Admin Sidebar

Products section now has a collapsible sub-menu (Alpine.js) with four links:
- Products
- Product Groups
- Configurable Options
- Promo Codes

Auto-expands when any product-related route is active.

---

## Key Implementation Details

### Pricing Save Logic (ProductController::savePricing)
- Loops through all active currencies × all billing cycles
- If input is blank → deletes the pricing row (disables that cycle)
- If input has a value → converts to cents and `updateOrCreate`s the row
- This makes it fully idempotent — the form always represents the full truth

### PromoCode::formattedValue()
Returns human-readable value: `"20.00%"` for percent, `"$5.00"` for fixed. Used in the admin table.

### PromoCode::isValid()
Checks: `is_active`, not over `max_uses`, within `start_date`/`end_date` range. Used to show Valid/Expired/Exhausted badges.

### Product::getPriceForCycle()
Helper to look up pricing for a specific currency + cycle from the loaded `pricing` relationship. Used in the show view.
