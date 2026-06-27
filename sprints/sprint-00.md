# Sprint 0 — Project Scaffold & CI

**Goal:** Get an empty-but-runnable Laravel 11 backend and React 19 frontend committed to git with a passing CI pipeline. Zero features — just a clean foundation the rest of the build stands on.

**Models:** Hermes = orchestrator (planning, routing, review relay). Coder = OpenClaw (glm-4.6).
**Estimate:** 3 issues · ~75-90 min each · assigned ONE AT A TIME.

## Issues

### #0.1 — Scaffold Laravel 11 backend
Create a fresh **Laravel 11.6.1** app in `backend/` (PHP 8.2). Install **Laravel Sanctum**. Configure MySQL 8 in `backend/.env.example` (`DB_DATABASE=pulsedesk`). Set up base `tests/TestCase.php` (RefreshDatabase + Sanctum `actingAs` helpers). Add `routes/api.php` with a health-check `GET /api/health` → `{ "status": "ok" }`. `.gitignore` for `vendor/`, `.env`.
- **Acceptance:** `php artisan serve` boots at :8000; `GET /api/health` → 200 JSON; `php artisan test` runs green (0 tests); `.env.example` + `composer.lock` present.

### #0.2 — Scaffold React 19 + Vite + Tailwind frontend
Create a **React 19 + Vite** app in `frontend/`. Install + configure **Tailwind CSS**. Add an axios API client reading `import.meta.env.VITE_API_URL`. Add `frontend/.env.example` with `VITE_API_URL=http://127.0.0.1:8000`. A landing page that pings `/api/health` and renders the result.
- **Acceptance:** `npm run dev` boots at :5173; landing page renders the health response; `npm run build` exits 0; `.env.example` present.

### #0.3 — Git init + GitHub Actions CI
Initialize git at repo root, create `main`, first commit. Add `.github/workflows/ci.yml`: on PR/push to `main` — PHP job (`composer install`, `php artisan test`) + Node job (`npm ci`, `npm run build`). Push to the GitHub remote (name: `forge2-pulsedesk-starter-kit` — **human to confirm the exact GitHub URL**). CI runs green on the initial commit.
- **Acceptance:** `git log` shows initial commit; remote configured; CI workflow exists and passes on first push.

## Outcome
- Shipped: _(fill after merge)_
- Slipped / moved: _
- PRs: _(fill)_
