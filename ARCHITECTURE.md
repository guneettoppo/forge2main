# PulseDesk — System Architecture

## Overview

PulseDesk is a multi-tenant support-desk SaaS. Multiple companies use it to manage customer support. Each company's data is fully isolated — every record belongs to an organization, and a user from Org A can never see Org B's data. All queries are scoped by `organization_id`.

---

## Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 + Sanctum + MySQL 8 |
| Frontend | React 19 + Vite + Tailwind CSS |
| Tests | PHPUnit feature tests |
| CI | GitHub Actions |
| Agents | Hermes (orchestrator) + OpenClaw (coder) via Slack channels |

---

## Backend Architecture

```
backend/
  app/
    Models/
      Organization.php
      User.php
      Ticket.php
      Comment.php
    Http/Controllers/Api/
      AuthController.php
      TicketController.php
      CommentController.php
    Policies/
      TicketPolicy.php
      CommentPolicy.php
    Middleware/
      EnsureTenantScope.php
    Traits/
      BelongsToOrganization.php
  routes/api.php
  database/
    factories/
    seeders/DatabaseSeeder.php
    migrations/
  tests/Feature/
```

### Data Model

```
Organization
  └─ Users (N)         roles: admin / agent / customer
  └─ Tickets (N)
       ├─ requester → User (customer)
       ├─ assignee → User (agent, nullable)
       ├─ status: open | pending | resolved | closed
       ├─ priority: low | medium | high | urgent
       └─ Comments (N)
            ├─ is_internal (agents only)
            └─ author → User
```

### Multi-Tenancy

- **Global Eloquent scope**: `BelongsToOrganization` auto-adds `WHERE organization_id = ?` to every query.
- **Middleware**: `EnsureTenantScope` resolves org ID from the auth token and sets it on the request.
- **Policies**: deny cross-tenant access at the controller layer.
- **No client-set org IDs**: `organization_id`, `requester_id` are server-set from the token.

---

## Frontend Architecture

```
frontend/
  src/
    api/client.js              # axios with auth interceptor (Bearer + 401)
    App.jsx
    pages/
      Login.jsx
      Register.jsx
      TicketList.jsx           # filters (status, priority, q) + cards
      TicketDetail.jsx         # ticket view + comments + reply box + internal note + status update
    components/Navbar.jsx
    index.css
```

### Role-Aware UI

| Capability | Customer | Agent | Admin |
|-----------|----------|-------|-------|
| Create ticket | ✅ | ✅ | ✅ |
| View own tickets | ✅ | ✅ | ✅ |
| Internal notes | ❌ | ✅ | ✅ |
| Assign tickets | ❌ | ✅ | ✅ |
| Change status | ❌ | ✅ | ✅ |

---

## API Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | /api/register | public | Create org + admin |
| POST | /api/login | public | Sanctum bearer |
| POST | /api/logout | sanctum | Invalidate token |
| GET | /api/tickets | sanctum | List (filters: status, priority, assignee, q) |
| POST | /api/tickets | sanctum | Create |
| GET | /api/tickets/{id} | sanctum | Show |
| PUT | /api/tickets/{id} | sanctum | Update (status, assignee, priority) |
| DELETE | /api/tickets/{id} | sanctum+admin | Delete |
| GET | /api/tickets/{id}/comments | sanctum | List |
| POST | /api/tickets/{id}/comments | sanctum | Create (internal toggle agents-only) |

---

## Agent Orchestration Loop

1. Human → #sprint-main: goal
2. Hermes → #agent-coder: assigns Issue #N
3. Coder → #agent-coder: opens PR, reports completion
4. Hermes → #human-approval: routes PR to Senior Dev
5. Senior Dev → #human-approval: APPROVE / CHANGES REQUESTED
6. Human merges or asks for changes
7. Repeat from step 2

**Agents:**

| Agent | Model | Role |
|-------|-------|------|
| Hermes | z-ai/glm-5.1 | Product Owner / orchestrator |
| Coder | z-ai/glm-4.6 | Implements, opens PRs |
| Senior Dev | z-ai/glm-5.1 | Reviews PRs |
| CI/CD | z-ai/glm-4.5-air | CI log reader + config fixes |

---

## Key Design Decisions

1. **Multi-tenancy by organization_id**: enforced at the database scope level, not just in controllers.
2. **SQLite in CI tests**: uses SQLite :memory: for speed and simplicity in GitHub Actions runners.
3. **Single-token auth**: Laravel Sanctum issues one bearer token per session; no refresh tokens for scope.
4. **Ticket model owns all time-boxed fields**: `resolved_at`, `closed_at` are on the ticket, not a separate state table.

---

## Running Locally

```bash
# backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

# frontend
cd frontend
npm install
npm run dev
```

Demo users (password: `password`):
- `admin@acme.test`, `agent1@acme.test`, `customer1@acme.test`
