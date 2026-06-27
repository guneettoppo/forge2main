# Sprint 4 — SHOULD tier (depth)

**Goal:** Add production-depth features on the solid MUST foundation: SLA policies + breach indicators, activity log/audit trail, dashboard metrics, and ticket queues. (Notifications + all STRETCH items deferred unless earlier sprints fly.)

**Models:** Hermes = orchestrator. Coder = OpenClaw (glm-4.6).
**Estimate:** 3 issues · ~75-90 min each · assigned ONE AT A TIME.
**Depends on:** Sprint 3 (MUST tier complete).

## Issues

### #4.1 — SLA policies + breach indicator
- **`sla_policies`:** organization_id, priority, response_minutes, resolution_minutes.
- On ticket create/update, compute first-response + resolution due times; expose `is_breached` / `due_at` in ticket JSON. Tenant-scoped.
- **Acceptance:** per-priority targets configured; tickets expose due times + breach flag; overdue tickets flagged; tests.

### #4.2 — Activity log / audit trail
- **`activity_logs`:** ticket_id, actor_id, action, meta (json), created_at.
- Log: created, status changed, priority changed, assigned, replied, internal note added. Expose `GET /api/tickets/{id}/activity` (agent/admin only). Tenant-scoped.
- **Acceptance:** every ticket mutation writes a log entry; activity endpoint returns timeline; customer cannot access; tests.

### #4.3 — Dashboard metrics + queues
- **`GET /api/dashboard/metrics`** (agent/admin): counts by status, by priority, avg first-response time, breach rate.
- **Queues:** `GET /api/tickets/unassigned`, `GET /api/tickets/mine`, `POST /api/tickets/{id}/claim`. All tenant-scoped.
- **Acceptance:** dashboard returns correct aggregates for the org; unassigned/mine/claim work and stay tenant-scoped; tests.

## Deferred (only if sprints fly)
- Notifications (in-app/webhook), canned responses, ticket merge, CSAT, customer portal, real-time updates, CSV export.

## Outcome
- Shipped: _(fill after merge)_
- Slipped / moved: _
- PRs: _(fill)_
