# Sprint 3 — Conversation, Filters & React Frontend

**Goal:** Complete the **MUST** tier. Threaded conversations (public replies + internal notes), filterable/searchable ticket list, and a working React UI that consumes the documented API. After this sprint the app is a usable mini-Zendesk.

**Models:** Hermes = orchestrator. Coder = OpenClaw (glm-4.6).
**Estimate:** 3 issues · ~75-90 min each · assigned ONE AT A TIME.
**Depends on:** Sprint 2 (tickets CRUD + seed).

## Issues

### #3.1 — Ticket comments / conversation
- **`comments`:** id, `ticket_id`, `author_id`, body, `is_internal` (bool), timestamps.
- **Endpoints:** `POST /api/tickets/{id}/comments` (public reply or internal note), `GET /api/tickets/{id}/comments`. Tenant-scoped. Thread ordered by `created_at`.
- **Policy:** customers never receive `is_internal=true` notes (filtered server-side).
- **Acceptance:** agents post internal notes invisible to customers; customers post public replies; GET returns ordered thread with internal notes hidden for customer role; tests.

### #3.2 — List filters + text search
- Extend `GET /api/tickets` with query params: `?status=`, `?priority=`, `?assignee_id=`, `?q=` (ILIKE on subject + description), pagination. Combine filters (AND). Tenant-scoped.
- **Acceptance:** each filter returns correct subset; combined filters work; `q` matches subject/description; cross-org never leaks; feature tests for each.

### #3.3 — React frontend (MUST-tier UI)
- **Pages:** Login; Ticket list (filters + search + pagination); Ticket detail (view + conversation thread + reply box + internal-note toggle for agents); Create ticket.
- Auth token stored; axios interceptor attaches `Bearer` + handles 401 → redirect to login. Role-aware UI (internal-note UI hidden for customers). Consumes documented API.
- **Acceptance:** user logs in as admin/agent/customer; browses + filters tickets; opens a ticket; posts a reply / internal note; creates a ticket; customer cannot see internal notes in UI.

## Outcome
- Shipped: _(fill after merge)_
- Slipped / moved: _
- PRs: _(fill)_
