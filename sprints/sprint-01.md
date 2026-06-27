# Sprint 1 — Multi-tenancy, Auth & Roles

**Goal:** Land the multi-tenant foundation. Organizations are tenants; every record carries `organization_id`; the tenant is derived from the **authenticated user** and queries are globally scoped. Sanctum auth with admin/agent/customer roles. **Org A can NEVER see Org B.** This is the NON-NEGOTIABLE gate.

**Models:** Hermes = orchestrator. Coder = OpenClaw (glm-4.6).
**Estimate:** 3 issues · ~75-90 min each · assigned ONE AT A TIME.

## Issues

### #1.1 — Organizations, Users, roles + global tenant scope
- **Migrations:** `organizations` (id, name, slug, timestamps); `users` (id, `organization_id` FK, name, email unique, email_verified_at, password, `role` enum[admin, agent, customer], timestamps). Sanctum `personal_access_tokens`.
- **Models:** `Organization`; `User` belongsTo Organization.
- **Tenant isolation:** a `BelongsToOrganization` trait + **global scope** that auto-applies `where organization_id = Auth::user()->organization_id` to every tenant-scoped model. **Tenant resolved from the authenticated user ONLY** — never a client-supplied header, param, or body field.
- **Acceptance:** migrations run clean; any model using the trait is auto-scoped by org; a feature test proves a query with NO explicit org filter still returns only the auth user's org records.

### #1.2 — Sanctum register / login / logout
- **Routes (`routes/api.php`):** `POST /api/register` (creates or joins an org, issues Sanctum token), `POST /api/login` (returns token + user + role + org), `POST /api/logout` (revokes token).
- **Controllers** under `app/Http/Controllers/Api/`. Validation + password hashing. 422 on bad input, 401 on bad credentials.
- **Acceptance:** register → 201 + token; login → 200 + token; authed routes work with `Bearer` token; logout revokes; feature tests cover happy path + 401 + 422.

### #1.3 — Tenant isolation: policies + hard tests (THE GATE)
- Global policy/middleware guaranteeing all tenant models are org-scoped.
- **Feature tests:** seed OrgA and OrgB, each with an admin + tickets. Assert OrgA admin listing tickets sees **0 OrgB tickets**; direct fetch of an OrgB ticket by an OrgA user → **404** (not 403, to avoid leaking existence).
- **Acceptance:** cross-org access always 404; zero leakage in any query path; tests green. **Senior Dev MUST sign off this issue specifically** as the tenancy gate before Sprint 2 starts.

## Outcome
- Shipped: _(fill after merge)_
- Slipped / moved: _
- PRs: _(fill)_
