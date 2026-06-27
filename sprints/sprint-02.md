# Sprint 2 — Tickets CRUD + Demo Seed

**Goal:** Build the core ticket model and full CRUD API — tenant-scoped and role-aware — plus a seeded demo dataset judges can log into.

**Models:** Hermes = orchestrator. Coder = OpenClaw (glm-4.6).
**Estimate:** 3 issues · ~75-90 min each · assigned ONE AT A TIME.
**Depends on:** Sprint 1 (tenant scope + auth).

## Issues

### #2.1 — Tickets model + migration
- **`tickets`:** id, `organization_id`, subject, description, `status`[open, pending, resolved, closed], `priority`[low, medium, high, urgent], `requester_id` (FK user), `assignee_id` (FK user, nullable), `tags` (json), timestamps.
- **Model** uses `BelongsToOrganization` trait; belongsTo requester + assignee. Casts for status/priority enums + tags json. Factory.
- **Acceptance:** migration runs; model auto-scopes by org; unit tests for casts + relations.

### #2.2 — Tickets API (index/store/show/update) + role rules
- **Endpoints:** `GET /api/tickets` (agent/admin see all in org; customer sees only own); `POST /api/tickets` (any authenticated; customer is auto-set as requester); `GET /api/tickets/{id}`; `PUT /api/tickets/{id}` (agent/admin change status/priority/assignee; customer cannot).
- **Policy** classes enforce roles. All tenant-scoped (cross-org → 404).
- **Acceptance:** full CRUD via API; customer sees only own tickets; agent/admin see all org tickets; cross-org → 404; feature tests per role.

### #2.3 — Database seeder (demo data)
- **`DatabaseSeeder`:** 1 org (Acme), 1 admin, 2 agents, 2 customers, ~12 tickets spread across statuses/priorities/assignees. Plus a second org (Globex) with 1 user + 2 tickets to prove isolation.
- Demo logins match README (`admin@acme.test`, `agent@acme.test`, `customer@acme.test` / `password`).
- **Acceptance:** `php artisan migrate --seed` loads exactly this; counts match spec; Globex data invisible to Acme tests.

## Outcome
- Shipped: _(fill after merge)_
- Slipped / moved: _
- PRs: _(fill)_
