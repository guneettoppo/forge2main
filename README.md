# PulseDesk — Multi-Tenant Support Desk SaaS

PulseDesk is a multi-tenant support-desk application (mini-Zendesk) built for **Forge 2 Main Edition**. Multiple organizations sign up, each gets an isolated workspace. Support agents manage tickets; customers submit requests.

**Core guarantee:** no cross-tenant data access. Every query is scoped by `organization_id`.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 + Sanctum + MySQL 8 |
| Frontend | React 19 + Vite + Tailwind CSS |
| Tests | PHPUnit feature tests |
| CI | GitHub Actions |
| Agents | Hermes + OpenClaw via Slack |

---

## Features

### MUST (complete)
- Multi-tenancy via `organization_id` scoping
- Auth & roles: admin, agent, customer (Laravel Sanctum)
- Tickets CRUD: subject, description, status, priority, requester, assignee, tags, timestamps
- Ticket conversation: public replies + internal notes (agents only)
- List + filters + search: filter by status/priority/assignee, text search on subject/body
- REST API + React frontend
- Seeded demo data: Acme + Globex orgs

### SHOULD (not started)
- SLA policies & timers
- Queues & assignment
- Activity log / audit trail
- Dashboard metrics
- Notifications

### STRETCH (not started)
- Canned responses, ticket merge, CSAT
- Customer portal, bulk actions, public rate-limited API
- Real-time updates, full-text search, CSV export

---

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8 (or SQLite for quick start)

### Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### Tests

```bash
cd backend
php artisan test
```

---

## API Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | /api/register | public | Create org + admin |
| POST | /api/login | public | Sanctum bearer token |
| POST | /api/logout | sanctum | Invalidate token |
| GET | /api/tickets | sanctum | List (filters: status, priority, assignee, q) |
| POST | /api/tickets | sanctum | Create |
| GET | /api/tickets/{id} | sanctum | Show |
| PUT | /api/tickets/{id} | sanctum | Update |
| DELETE | /api/tickets/{id} | sanctum+admin | Delete |
| GET | /api/tickets/{id}/comments | sanctum | List |
| POST | /api/tickets/{id}/comments | sanctum | Create (internal toggle agents-only) |

---

## Demo Credentials

Password for all: `password`

| Email | Role |
|-------|------|
| admin@acme.test | admin |
| agent1@acme.test | agent |
| agent2@acme.test | agent |
| customer1@acme.test | customer |
| customer2@acme.test | customer |
| admin@globex.test | admin (isolation proof) |
| cust@globex.test | customer (isolation proof) |

---

## Agent Orchestration

| Agent | Model | Role | Channel |
|-------|-------|------|---------|
| **Hermes** | z-ai/glm-5.1 | Product Owner / orchestrator | #sprint-main, #agent-coder |
| **Coder** | z-ai/glm-4.6 | Implements, opens PRs | #agent-coder |
| **Senior Dev** | z-ai/glm-5.1 | Reviews PRs | #human-review |
| **CI/CD** | z-ai/glm-4.5-air | CI log reader + config fixes | #ci-cd |

### Loop

1. Human → #sprint-main: goal
2. Hermes → #agent-coder: assigns Issue #N
3. Coder → #agent-coder: opens PR, reports completion
4. Hermes → #human-review: routes PR to Senior Dev
5. Senior Dev → #human-review: APPROVE / CHANGES REQUESTED
6. Human merges or asks for changes
7. Repeat from step 2

---

## Repository Structure

- Repo: `forge2-pulsedesk-starter-kit`
- Local path: `C:\Users\Guneet Toppo\Desktop\forge2-pulsedesk-starter-kit`

```
forge2-pulsedesk-starter-kit/
  backend/
    app/
      Models/         Organization, User, Ticket, Comment
      Http/Controllers/Api/  Auth, Ticket, Comment
      Policies/       TicketPolicy, CommentPolicy
      Middleware/     EnsureTenantScope
      Traits/         BelongsToOrganization
    routes/api.php
    database/
      factories/
      seeders/DatabaseSeeder.php
      migrations/
    tests/Feature/    AuthTest, TenantScopeTest, TicketApiTest
  frontend/
    src/
      api/client.js
      pages/          Login, Register, TicketList, TicketDetail
      components/     Navbar
  agents/             Hermes, Coder, Senior Dev, CI/CD configs
  slack-export/       Autonomous agent communication logs
  ARCHITECTURE.md     System design
  agent-log.md        Agent communication summary
  project-state.json  Current sprint/feature status
```

---

## Judging Criteria Mapping

| Criterion | Weight | Evidence |
|-----------|--------|----------|
| Agent orchestration loop | 20 | 4 Slack channels; Hermes routes PRs; human merges only after approval |
| OpenClaw config & coder execution | 16 | 4 agents in repo; model assignments; Coder status reports |
| App works end-to-end | 14 | Laravel 11 + Sanctum API; React 19 SPA; seeded demo data |
| Multi-tenant isolation | 12 | Global scope + middleware + policies; cross-tenant 404 in tests |
| Hermes orchestration & PO setup | 10 | Sprint plans in `sprints/`; backlog in #sprint-main |
| Sprints → CI/CD → human-merged PRs | 10 | 3 completed sprints; GitHub Actions CI; Senior Dev approval |
| Slack workspace + export quality | 6 | 5 public channels; `agent-log.md` + `slack-export/` JSON |
| Code & architecture | 5 | Controllers → policies → models; REST API + React SPA |
| Repo & docs | 4 | Public repo; README + ARCHITECTURE.md + agent-log.md |
| Live demo (in-person) | 3 | Demo logins; Acme + Globex orgs ready |

---

## Repo

**GitHub:** https://github.com/guneettoppo/forge2main



# Judging Criteria Mapping

# This section maps the project to the judging rubric.

# | Criterion | Weight | How PulseDesk satisfies it |
# |-----------|--------|---------|
# | Agent orchestration loop (Hermes \u2194 OpenClaw \u2194 you) | 20 | 4 Slack channels: #sprint-main, #agent-coder, #human-review, #ci-cd. Hermes routes PRs from Coder to Senior Dev. Human merges only after approval. |
# | OpenClaw configuration \& coder execution | 16 | 4 agents defined in repo (agents/): Hermes, Coder, Senior Dev, CI\\/CD. Model assignments: Hermes/glm-5.1, Coder/glm-4.6, SrDev/glm-5.1, CI/glm-4.5-air. Coder reports "What I Did / What's Left / What Needs Your Call" after every issue. |
# | App works end-to-end | 14 | Laravel 11 + Sanctum backend serving JSON API. React 19 + Vite + Tailwind frontend. Login/Register + TicketList + TicketDetail. Seeded demo data. |
# | Multi-tenant isolation | 12 | BelongsToOrganization global scope + EnsureTenantScope middleware + TicketPolicy + CommentPolicy. Server-set organization_id and requester_id. Separate demo orgs (Acme + Globex). Cross-tenant 404 proven in tests. |
# | Hermes orchestration \& PO setup | 10 | Hermes owns backlog in #sprint-main. Creates sprint plans (sprints/). Breaks work into 75-90 min issues. Assigns one at a time. Monitors #agent-log + #ci-cd. |
# | Sprints \u2192 CI\/CD \u2192 human-merged PRs | 10 | 3 completed sprints (Sprint 0\u20132). GitHub Actions CI on every PR. CI\/CD agent in #ci-cd diagnoses failures. Senior Dev approves. Human merges via #human-review. |
# | Slack workspace + export quality | 6 | 5 public Slack channels. All agent comms captured as Slack exports (slack-export/). Structured JSON exports + markdown summary in agent-log.md. |
# | Code \& architecture | 5 | Layered architecture: controllers \u2192 policies \u2192 models \u2192 DB. REST API + React SPA. Comments use threaded is_internal. Ticket status with resolved_at/closed_at. JsonResource::withoutWrapping() for clean JSON. |
# | Repo \& docs | 4 | Public repo at github.com/guneettoppo/forge2main. README + ARCHITECTURE.md + agent-log.md + project-state.json + Slack exports + seeder + tests all committed. |
# | Live demo (in-person) | 3 | Demo logins provided (password: password). Acme + Globex orgs ready. Trained models: admin@acme.test, agent1@acme.test, customer1@acme.test. |

