# Context

## Summary

Pizza Planet is ready to scaffold the frontend application. The repo has Devbox in place and `frontend/` currently contains only a placeholder `.gitignore`.

The frontend should be a standalone Vue 3 + TypeScript + Tailwind CSS + Vite app under `frontend/`, with tests through Vitest and code quality through StandardJS with ts-standard.

## Decisions

- Decision: Keep the frontend scaffold plan under `.docs/agent-work/task/frontend-scaffold/`.
  - Reason: Existing project planning artifacts live under `.docs/agent-work/task/`.
  - Date: 2026-06-02

- Decision: Treat this as frontend-only scaffold work.
  - Reason: The user asked to scaffold the frontend application now that Devbox is set up; backend Laravel scaffolding is related but separate work.
  - Date: 2026-06-02

- Decision: Keep frontend and backend code separate.
  - Reason: Project architecture requires frontend code under `frontend/`, backend code under `backend/`, and a clear boundary between the two.
  - Date: 2026-06-02

- Decision: Use browser-visible `VITE_` environment variables only for non-secret frontend configuration.
  - Reason: Vite exposes these values to the built browser app, so secrets do not belong there.
  - Date: 2026-06-02

- Decision: Defer Pinia unless the first implementation pass finds a scaffold-level reason to include it.
  - Reason: The architecture reserves `src/stores`, but no product state exists yet.
  - Date: 2026-06-02

- Decision: Use a small typed `fetch` wrapper instead of Axios.
  - Reason: The scaffold only needs a Laravel API boundary, not interceptors or extra runtime dependencies yet.
  - Date: 2026-06-02

- Decision: Defer browser/e2e tooling.
  - Reason: Vitest covers the scaffold smoke test, and no user workflow exists yet to justify Playwright.
  - Date: 2026-06-02

- Decision: Track `frontend/pnpm-workspace.yaml` with `esbuild` build approval.
  - Reason: pnpm 11 blocks unapproved dependency build scripts, and Vite needs `esbuild` postinstall to prepare the local binary.
  - Date: 2026-06-02

## Discoveries

- Discovery: Frontend architecture expects `src/app`, `src/assets`, `src/layouts`, `src/pages`, `src/features`, `src/shared`, and `src/stores`.
  - Source: `.docs/frontend/ARCHITECTURE.md`
  - Impact: The scaffold should create this shape up front instead of using the raw Vite template layout.

- Discovery: Shared API infrastructure belongs under `frontend/src/shared/api`, and feature-specific API calls belong under `frontend/src/features/<feature>/api`.
  - Source: `.docs/frontend/ARCHITECTURE.md`
  - Impact: The scaffold should include a shared API boundary and avoid request logic inside Vue components.

- Discovery: Frontend code must use TypeScript, Composition API, and `<script setup>`.
  - Source: `.docs/frontend/CODE-QUALITY.md`
  - Impact: Vue files in the scaffold should avoid Options API patterns.

- Discovery: Tests should use Vitest and follow fail-first habits.
  - Source: `.docs/WRITING-TESTS.md`
  - Impact: Include at least one scaffold test and wire test commands immediately.

- Discovery: Devbox provides Node.js 24 and pnpm.
  - Source: `devbox.json`
  - Impact: Frontend package setup should use pnpm and current tooling compatible with Node.js 24.

- Discovery: Laravel's official release notes currently list Laravel 13 as supporting PHP 8.3 through 8.5, with Laravel 12 security-supported until February 24, 2027.
  - Source: `https://laravel.com/docs/13.x/releases`
  - Impact: The frontend scaffold can proceed, while backend scaffold planning should explicitly choose Laravel 12 or 13 later.

## Security Review

- Asset: Browser-exposed frontend configuration.
  - Risk: Accidentally placing secrets in `VITE_` variables would expose them to every user of the built app.
  - Mitigation: Only include non-secret values in `frontend/.env.example`; never track real `.env` files.

- Asset: Future Laravel API calls.
  - Risk: Treating backend responses or user-controlled route/query/input values as trusted can create rendering, auth, or data handling bugs later.
  - Mitigation: Keep request logic in `shared/api` and feature API modules; validate and encode data at use boundaries.

- Asset: Dependency supply chain.
  - Risk: Scaffolding pulls third-party packages into the app.
  - Mitigation: Use mainstream packages, commit `pnpm-lock.yaml`, and keep generated package changes reviewable.

- Asset: Local service credentials.
  - Risk: Frontend code could accidentally depend on MySQL or Redis credentials from Devbox.
  - Mitigation: Do not pass database, Redis, or server credentials into frontend env files.

## Changes in Direction

- Change: Use `happy-dom` for Vitest instead of jsdom.
  - Previous approach: Either jsdom or happy-dom was acceptable in the plan.
  - New approach: Use `happy-dom`.
  - Reason: It is enough for the scaffold render test and keeps the test environment lightweight.

## Blockers

- Blocker: None for frontend scaffold planning.
  - Impact: Implementation can proceed once approved.
  - Possible resolution:

## Notes

- Root `README.md` already documents `cd frontend && pnpm install`, `pnpm dev`, `pnpm test`, `pnpm lint`, and `pnpm typecheck` as intended steady-state frontend commands.
- Existing `frontend/.gitignore` already ignores dependencies, build output, Vite cache, logs, coverage, package manager caches, and real env files while allowing `.env.example`.
- Backend Laravel app selection remains a separate decision. The current frontend plan should avoid assuming concrete backend routes.
- Implemented `frontend/` with Vue 3, TypeScript, Vite, Tailwind CSS, Vue Router, Vitest, vue-tsc, and ts-standard.
- Added `frontend/src/shared/api/client.ts` as the shared API boundary. Feature API modules should build on this instead of placing request logic in components.
- Verification passed on 2026-06-02: `pnpm lint`, `pnpm typecheck`, `pnpm test`, `pnpm build`, and browser render check at `http://127.0.0.1:5173/`.
- Browser verification found the home page heading and stack-check item exactly once.
