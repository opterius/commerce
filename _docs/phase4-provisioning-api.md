# Phase 4 — Provisioning & Panel API

**Status:** Not started

Provisioning is Commerce's killer feature — automatic hosting account lifecycle management via the Opterius Panel API. When a client pays, their hosting account is created automatically. When they stop paying, it's suspended. When they pay again, it's unsuspended.

---

## Scope

### Server Management
- **Server records** — admin registers Panel servers in Commerce (hostname, IP, API URL, API token, max accounts)
- **Server groups** — logical groupings (e.g., "EU Servers", "US Servers")
- **Capacity tracking** — Commerce reads account count and server load from Panel API
- **Auto-assign** — on order, Commerce picks the server with the most free capacity in the matching server group

### Provisioning Module: Opterius Panel
- **Create** — on payment, Commerce calls Panel API to create a hosting account (username, password, domain, plan/package)
- **Suspend** — on overdue (after grace period), Commerce calls Panel API to suspend the account
- **Unsuspend** — on payment received for an overdue invoice, Commerce calls Panel API to unsuspend
- **Terminate** — after extended grace period (configurable), Commerce calls Panel API to terminate/delete the account
- **Manual actions** — admin can trigger create/suspend/unsuspend/terminate manually from the service detail page

### Product → Server Mapping
- Each product with `provisioning_module = 'opterius_panel'` is linked to a server group
- The product defines the hosting plan/package name on the Panel side
- Configurable options map to Panel account attributes (disk, bandwidth, email accounts, etc.)

### Service Lifecycle

```
Order Paid → Create Account → Active
                                 ↓ (non-payment)
                            Suspended
                                 ↓ (payment received)
                            Unsuspended → Active
                                 ↓ (grace period expired)
                            Terminated
```

---

## Database Tables (Planned)

| Table | Purpose |
|-------|---------|
| `servers` | Panel server records (name, hostname, ip, api_url, api_token, max_accounts, server_group_id) |
| `server_groups` | Logical groupings (name, description) |
| `services` (extends Phase 3) | Add: server_id, username, panel_account_id, suspended_at, terminated_at |
| `provisioning_log` | Log of all provisioning actions (service_id, action, status, response, created_at) |

---

## Panel API Integration

Commerce communicates with Panel servers using HMAC-signed HTTP requests (same authentication pattern the Panel agent uses internally).

### Endpoints Commerce Calls

| Action | Method | Panel API Endpoint |
|--------|--------|-------------------|
| Create account | POST | `/api/accounts` |
| Suspend account | POST | `/api/accounts/{id}/suspend` |
| Unsuspend account | POST | `/api/accounts/{id}/unsuspend` |
| Terminate account | DELETE | `/api/accounts/{id}` |
| Get server stats | GET | `/api/server/stats` |
| Get account list | GET | `/api/accounts` |
| Get account detail | GET | `/api/accounts/{id}` |

### Payload for Account Creation

```json
{
    "username": "client123",
    "password": "generated-secure-password",
    "domain": "example.com",
    "package": "starter",
    "email": "client@example.com",
    "disk_limit_mb": 10240,
    "bandwidth_limit_mb": 102400
}
```

### Queue-Based Provisioning
All provisioning actions run via Laravel queued jobs:
- `CreateHostingAccountJob`
- `SuspendHostingAccountJob`
- `UnsuspendHostingAccountJob`
- `TerminateHostingAccountJob`

Jobs retry on failure (max 3 attempts, exponential backoff). Failed provisioning triggers an admin notification.

---

## Admin Pages

- **Servers** — CRUD for Panel servers, connection test button, account count display
- **Server Groups** — CRUD for logical groupings
- **Service Detail** — shows provisioning status, Panel account info, manual action buttons
- **Provisioning Log** — filterable log of all create/suspend/unsuspend/terminate actions

## Scheduling (Cron)

- **Suspension check** — runs daily, finds overdue services past grace period, queues suspension jobs
- **Termination check** — runs daily, finds suspended services past termination grace period, queues termination jobs
- **Unsuspension check** — runs on payment received (event-driven, not cron)

---

## Key Technical Decisions

### First-Class Panel Support
The Opterius Panel module is built directly into Commerce — not as a plugin. It must be the best provisioning module, better than any third-party module could be. This is what makes Commerce the obvious choice for Panel users.

### Other Panels (Phase 2 / Stage 2)
cPanel, DirectAdmin, Plesk modules come later as **paid, closed-source** add-ons. They use the same provisioning interface but are distributed separately.

### Provisioning Interface
All modules implement a common interface:

```php
interface ProvisioningModule
{
    public function createAccount(Service $service): ProvisioningResult;
    public function suspendAccount(Service $service): ProvisioningResult;
    public function unsuspendAccount(Service $service): ProvisioningResult;
    public function terminateAccount(Service $service): ProvisioningResult;
    public function getAccountInfo(Service $service): ?array;
    public function testConnection(Server $server): bool;
}
```
