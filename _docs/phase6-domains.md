# Phase 6 — Domain Registration

**Status:** Not started

Adds a full domain registrar module so hosting companies can sell domain registrations, transfers, and renewals alongside hosting — competing directly with WHMCS on this feature.

---

## Registrar: ResellerClub (LogicBoxes)

**Why ResellerClub:**
- HTTP JSON API (no XML parsing)
- Auth: `auth-userid` + `api-key` query params
- Sandbox: `https://test.httpapi.com/api/`
- Production: `https://httpapi.com/api/`
- Widest TLD catalog of any reseller API
- Same API used by WHMCS

**Key API endpoints used:**
| Endpoint | Purpose |
|---|---|
| `GET domains/available.json` | Availability check (bulk) |
| `POST domains/register.json` | Register domain |
| `POST domains/renew.json` | Renew domain |
| `POST domains/transfer.json` | Transfer domain in |
| `GET domains/details.json` | Sync domain info |
| `POST domains/modify-ns.json` | Update nameservers |
| `POST domains/modify-privacy-protection.json` | Toggle WHOIS privacy |
| `POST domains/enable-theft-protection.json` | Lock domain |
| `POST domains/disable-theft-protection.json` | Unlock domain |
| `GET domains/actions/get-transfer-authcode.json` | Get EPP code |
| `POST contacts/add.json` | Create registrar contact |
| `GET contacts/details.json` | Get contact details |

---

## Database Tables

| Table | Purpose |
|---|---|
| `domain_tlds` | TLD catalog with per-TLD pricing and settings |
| `domains` | Registered domain records (parallel to services) |
| `domain_contacts` | Per-domain WHOIS contacts (registrant/admin/tech/billing) |

### `domain_tlds`
```
id, tld (e.g. "com", "net", "co.uk"), is_active, sort_order
register_price (cents/year), renew_price (cents/year), transfer_price (cents)
min_years (default 1), max_years (default 10)
epp_required (bool), whois_privacy_available (bool)
grace_period_days, redemption_period_days
currency_code
timestamps
```

### `domains`
```
id
client_id FK → clients
order_id nullable FK → orders
order_item_id nullable FK → order_items
domain_name (full: "example.com")
tld (e.g. "com")
status: pending | active | expired | transferred_away | cancelled | fraud | redemption
registrar_module (e.g. "resellerclub")
registrar_order_id (registrar's internal order ID)
registration_date (date)
expiry_date (date)
auto_renew (bool, default true)
whois_privacy (bool, default false)
is_locked (bool, default true)
epp_code (nullable, stored temporarily for transfer-out)
ns1, ns2, ns3, ns4 (nullable strings)
billing_cycle (1year, 2year, 3year, 5year, 10year)
amount (cents, price paid)
currency_code
next_due_date (date)
last_due_date (date, nullable)
notes (text, nullable)
timestamps
```

### `domain_contacts`
```
id
domain_id FK → domains
type: registrant | admin | tech | billing
registrar_contact_id (registrar's contact ID, nullable)
first_name, last_name
company (nullable)
email
phone (E.164 format)
address_1, address_2 (nullable)
city, state (nullable), postcode, country_code
timestamps
```

---

## Product Type: `domain`

Add `domain` to the existing product type enum (`hosting | domain | other`). Domain products:
- Use TLD-based pricing from `domain_tlds` instead of `product_pricing`
- Have a `tld` field on the product (links to a specific TLD)
- Billing cycles map to registration periods (1year, 2year, etc.)

---

## Contracts

### `DomainRegistrarModule` interface
```php
checkAvailability(string $sld, string $tld): DomainCheckResult
checkBulkAvailability(string $sld, array $tlds): array  // keyed by TLD
register(Domain $domain, array $contacts, int $years): DomainResult
renew(Domain $domain, int $years): DomainResult
transfer(Domain $domain, string $eppCode, array $contacts): DomainResult
getDomainInfo(Domain $domain): DomainResult
updateNameservers(Domain $domain, array $nameservers): DomainResult
getEppCode(Domain $domain): DomainResult
setLock(Domain $domain, bool $locked): DomainResult
setPrivacy(Domain $domain, bool $enabled): DomainResult
testConnection(): DomainResult
```

### DTOs
- `DomainCheckResult` — domain, tld, available (bool), premium (bool), price (nullable)
- `DomainResult` — success (bool), message, data (array), error (string)

---

## Registrar Module: ResellerClub

`app/Registrar/Modules/ResellerClubModule.php`

Contacts flow:
1. Before registering, check if registrant contact exists in `domain_contacts` with a `registrar_contact_id`
2. If not, create contact via `contacts/add.json` → store returned ID
3. Use same contact for admin/tech/billing (can be configured)
4. Register domain with all four contact IDs

Settings stored in `settings` table (group: `registrar`):
- `registrar_module` — active module slug
- `resellerclub_auth_userid`
- `resellerclub_api_key`
- `resellerclub_sandbox` (bool)

---

## Jobs

| Job | Trigger | Retries |
|---|---|---|
| `RegisterDomainJob` | Invoice paid (domain product) | 3 × 60s backoff |
| `RenewDomainJob` | Invoice paid (domain renewal) | 3 × 60s backoff |
| `TransferDomainJob` | Invoice paid (transfer product) | 3 × 60s backoff |
| `SyncDomainJob` | Manual admin action / daily cron | 2 × 120s backoff |

---

## Commands

| Command | Schedule | Purpose |
|---|---|---|
| `commerce:check-expiring-domains` | Daily 04:00 | Generate renewal invoices for domains expiring in ≤30 days |
| `commerce:sync-domain-statuses` | Daily 05:00 | Sync all active domains with registrar API to catch external changes |

---

## Admin Pages

- **Domains list** — filterable by status, TLD, client, registrar; shows expiry, auto-renew badge
- **Domain detail** — info panel, nameserver form, WHOIS privacy toggle, lock toggle, EPP code reveal, contact details, registrar sync button, renewal history
- **TLD Manager** — CRUD for `domain_tlds`; columns: TLD, register/renew/transfer price, active, privacy, EPP
- **Registrar Settings** — API credentials form (auth-userid, api-key, sandbox toggle, test connection button) in Settings → Registrar tab

---

## Client Portal Pages

- **Domain Search** (`/client/domains/search`) — enter domain name, check across TLDs, shows availability + prices, "Register" button per result
- **My Domains** (`/client/domains`) — list with status badge, expiry date, auto-renew toggle
- **Domain Detail** (`/client/domains/{domain}`) — nameserver management, WHOIS privacy toggle, EPP code request, auto-renew toggle, registrant contact view

---

## Order Flow Integration

1. Client visits domain search → selects TLD → hits "Register"
2. Redirects to `/client/order/domain` with `domain` + `tld` pre-filled
3. Order form: confirm domain, select years (1-10), enter/confirm registrant contact
4. On submit → `Order` + `OrderItem` created with `type=domain`
5. `InvoiceService::createForOrder()` generates invoice
6. On payment → `InvoiceService::markPaid()` dispatches `RegisterDomainJob`
7. Job creates `Domain` record, calls registrar API, updates `status → active`

---

## Key Technical Decisions

### Contacts
Use the client's profile as default registrant contact data. Store per-domain in `domain_contacts`. Create registrar-side contacts via API on first registration and cache the `registrar_contact_id` for reuse.

### EPP Code
Never store permanently. Fetch on demand from registrar API and display to client once. Don't cache.

### Auto-renew vs Invoice-based renewal
- Auto-renew at the registrar level is kept **OFF** by default — Commerce manages renewals via invoice
- Commerce generates renewal invoices 30 days before expiry (`check-expiring-domains` command)
- On payment, dispatches `RenewDomainJob`
- If invoice goes unpaid, domain expires at registrar (no auto-charge)

### Pricing
- TLD prices in `domain_tlds` are cost prices (what ResellerClub charges you)
- Admin sets retail prices in `domain_tlds.register_price` etc. — these are what clients pay
- Currency follows the client's default currency

### Sandbox
`resellerclub_sandbox = true` routes all API calls to `test.httpapi.com`. Toggle in Settings → Registrar.

---

## Future Enhancements (Stage 2)

- **Domain transfer out** — detect incoming transfer, release domain on confirmation
- **WDRP / grace period** — detect redemption state, offer restore at premium price
- **Premium domains** — detect premium flag from availability check, show premium price
- **Bulk domain search** — search many TLDs at once from a single name
- **DNS management** — point to registrar DNS, manage records through Commerce
- **Email forwarding** — per-domain email forwarders via registrar DNS
