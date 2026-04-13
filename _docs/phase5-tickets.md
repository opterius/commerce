# Phase 5 — Support Tickets

**Status:** Not started

The ticket system gives hosting companies a built-in help desk so they don't need a separate tool like osTicket, Freshdesk, or Zendesk.

---

## Scope

### Ticket System
- **Departments** — organize tickets by topic (e.g., Sales, Billing, Technical Support)
- **Priorities** — low, medium, high, urgent
- **Statuses** — open, answered (staff replied), customer_reply (client replied), on_hold, closed
- **Assignment** — assign tickets to specific staff members
- **Tags** — categorize tickets for reporting

### Client-Facing
- **Create ticket** — client selects department, enters subject + message, attaches files
- **View tickets** — list of own tickets with status badges, click to view thread
- **Reply** — add a reply with file attachments
- **Close** — client can close their own tickets

### Staff-Facing
- **Ticket list** — filterable by department, status, priority, assigned staff, client
- **Ticket detail** — full conversation thread, internal notes (not visible to client), assignment, priority/status changes
- **Canned responses** — pre-written reply templates that staff can insert
- **Merge** — merge duplicate tickets
- **Move** — move ticket to a different department

### Email Piping
- **Inbound** — replies to ticket notification emails create ticket updates (parse `In-Reply-To` header or `+ticketid` addressing)
- **Outbound** — all ticket replies and status changes send email notifications to the client
- **Per-department email** — each department can have its own sender address

### File Attachments
- **Upload** — clients and staff can attach files to tickets and replies
- **Storage** — stored in `storage/app/ticket-attachments/` (not public)
- **Limits** — configurable max file size and allowed extensions
- **Download** — authenticated download via signed URL

### Notifications
- **Client:** new reply from staff, ticket closed, ticket re-opened
- **Staff:** new ticket, new client reply, ticket assigned to you
- **Admin:** configurable notification rules (e.g., notify all admins on urgent tickets)

---

## Database Tables (Planned)

| Table | Purpose |
|-------|---------|
| `ticket_departments` | Departments (name, email, description, is_active, sort_order) |
| `tickets` | Ticket records (client_id, department_id, assigned_staff_id, subject, status, priority, last_reply_at) |
| `ticket_replies` | Conversation thread (ticket_id, staff_id or client_id, body, is_internal_note) |
| `ticket_attachments` | File attachments (ticket_reply_id, filename, original_name, mime_type, size, path) |
| `canned_responses` | Pre-written templates (title, body, department_id nullable, staff_id nullable) |
| `ticket_tags` | Tags for tickets |
| `ticket_tag` | Pivot: ticket ↔ tag |

---

## Admin Pages

- **Tickets** — main ticket list with filters (department, status, priority, assigned, client search)
- **Ticket Detail** — conversation thread with reply form, internal note toggle, assignment dropdown, status/priority selectors, attachment uploads
- **Departments** — CRUD for departments
- **Canned Responses** — CRUD for response templates (global or per-department)
- **Ticket Settings** — auto-close after X days inactive, default priority, max attachment size, allowed extensions

## Client Portal Pages

- **My Tickets** — list of own tickets with status badges
- **View Ticket** — conversation thread with reply form, file attachments
- **Open Ticket** — department selector, subject, message, file upload, related service selector (optional)

---

## Key Technical Decisions

### Status Flow

```
Client creates ticket    → open
Staff replies            → answered
Client replies           → customer_reply
Staff puts on hold       → on_hold
Either party closes      → closed
Client replies to closed → re-opens as customer_reply
```

### Internal Notes
Staff can add internal notes (yellow background in UI) that are not visible to the client. Useful for escalation context, debugging notes, etc.

### Email Piping Strategy
Two approaches (choose one during implementation):

1. **Mailgun/SES inbound webhook** — email provider forwards inbound mail as an HTTP POST to Commerce. Simpler, no server mail config needed.
2. **IMAP polling** — Commerce polls an IMAP mailbox on a schedule. More portable, works with any email provider.

Option 1 is recommended for simplicity.

### Canned Responses
Templates support variables: `{client_name}`, `{client_email}`, `{ticket_id}`, `{department}`. Staff selects a canned response from a dropdown, it pre-fills the reply textarea, staff can edit before sending.

### Auto-Close
A scheduled job runs daily and closes tickets that have been in `answered` status for more than X days (configurable, default 5 days) without a client reply. Sends a "ticket auto-closed" email to the client.

---

## Future Enhancements (Stage 2)

- **SLA tracking** — first response time, resolution time, per-department SLA targets
- **Satisfaction ratings** — client rates the support experience after ticket closure
- **Knowledge base integration** — suggest relevant KB articles when creating a ticket
- **Slack/Discord integration** — notify a channel on new tickets
- **AI-suggested responses** — use Claude API to suggest replies based on ticket context
