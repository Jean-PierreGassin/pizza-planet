# Objective

Scaffold the standalone frontend application under `frontend/` using Vue 3, TypeScript, Tailwind CSS, Vite, Vitest, and StandardJS with ts-standard.

# Scope

This work covers the initial frontend project structure, dependency setup, build/test/lint scripts, Tailwind wiring, Vite configuration, Vue app bootstrap, routing, test harness, environment examples, and lightweight placeholder UI that proves the stack works.

This work does not implement product features, backend endpoints, authentication, database schema, deployment, or a Laravel app scaffold. Backend integration should be limited to documenting the expected API boundary and configuring non-secret local API base values.

# Acceptance Criteria

- [x] `frontend/` contains a working Vue 3 + TypeScript + Vite application.
- [x] Tailwind CSS is installed, configured, and applied through the frontend style entrypoint.
- [x] The source tree follows `.docs/frontend/ARCHITECTURE.md`, including `src/app`, `src/pages`, `src/layouts`, `src/features`, `src/shared`, `src/stores`, and `src/assets`.
- [x] Vue Router is installed and configured with at least one route rendered through `App.vue`.
- [x] API infrastructure is scaffolded under `frontend/src/shared/api` without placing request logic in Vue components.
- [x] Test setup uses Vitest, Vue Test Utils, and jsdom or happy-dom with at least one passing component or page test.
- [x] Static checks are wired through package scripts for `lint`, `typecheck`, `test`, and `build`.
- [x] StandardJS with ts-standard is configured for TypeScript and Vue source files.
- [x] `frontend/.env.example` documents public, non-secret frontend configuration only.
- [x] Generated dependency, build, coverage, and env files remain ignored by Git.
- [x] README setup commands for `frontend/` remain accurate after scaffolding.

# Phases

## Phase 1: Confirm Scaffold Baseline

Goal:

Pick the exact frontend generator and package set before writing app files.

Tasks:

- [x] Confirm `frontend/` only contains intentional placeholder files before scaffolding.
- [x] Use pnpm from Devbox as the frontend package manager.
- [x] Scaffold with Vite's Vue + TypeScript template or manually create an equivalent minimal app if the generator cannot target the existing directory cleanly.
- [x] Prefer current stable packages compatible with Node.js 24 and Vite.
- [x] Keep Laravel integration out of the scaffold except for a documented API base URL convention.

## Phase 2: Install Frontend Tooling

Goal:

Create a package manifest and lockfile that support development, checks, and production builds.

Tasks:

- [x] Add runtime dependencies for Vue 3 and Vue Router.
- [x] Add development dependencies for Vite, TypeScript, Vue plugin tooling, Tailwind CSS, PostCSS, Autoprefixer, Vitest, Vue Test Utils, jsdom or happy-dom, ts-standard, and Vue type checking.
- [x] Add `packageManager` metadata for pnpm.
- [x] Add scripts for `dev`, `build`, `preview`, `test`, `test:watch`, `lint`, and `typecheck`.
- [x] Generate `pnpm-lock.yaml`.

## Phase 3: Build The App Skeleton

Goal:

Create the expected frontend directory shape with a tiny but real application.

Tasks:

- [x] Add `index.html`, `vite.config.ts`, TypeScript configs, Tailwind config, and PostCSS config.
- [x] Add `src/app/main.ts`, `src/app/App.vue`, `src/app/router`, and provider placeholders if needed.
- [x] Add `src/assets/styles/app.css` and import Tailwind layers there.
- [x] Add `src/layouts/DefaultLayout.vue`.
- [x] Add `src/pages/HomePage.vue`.
- [x] Add empty `.gitkeep` files where useful for planned architecture directories with no implementation yet.
- [x] Keep components Composition API-only with `<script setup>`.

## Phase 4: Add Shared API Boundary

Goal:

Make the Laravel API contract boundary explicit without coupling the frontend to endpoints that do not exist yet.

Tasks:

- [x] Add `frontend/src/shared/api/client.ts` with a small typed wrapper around `fetch` or a similarly minimal API client.
- [x] Read API base URL from a `VITE_` environment value only.
- [x] Add `frontend/.env.example` with local placeholder values such as `VITE_API_BASE_URL=http://127.0.0.1:8000/api`.
- [x] Do not include secrets, private tokens, database credentials, or server-only values in frontend env files.
- [x] Ensure API errors are typed enough for future feature API modules to consume.

## Phase 5: Wire Tests And Quality Checks

Goal:

Prove the scaffold can be maintained before product code is added.

Tasks:

- [x] Configure Vitest for Vue single-file components.
- [x] Add a simple render test for `App.vue`, `DefaultLayout.vue`, or `HomePage.vue`.
- [x] Configure TypeScript type checking for `.vue` files.
- [x] Configure ts-standard so linting covers TypeScript and Vue files consistently.
- [x] Run and fix `pnpm lint`, `pnpm typecheck`, `pnpm test`, and `pnpm build`.

## Phase 6: Document And Verify Local Usage

Goal:

Leave the repo in a state where another developer can install and run the frontend from Devbox.

Tasks:

- [x] Update `frontend/README.md` with install, run, build, test, lint, typecheck, and environment setup commands.
- [x] Confirm root `README.md` frontend commands still match the scaffold.
- [x] Run `devbox run preflight` if runtime drift is suspected.
- [x] Optionally start `pnpm dev` from `frontend/` and verify the app in the browser.
- [x] Confirm `git status --short` contains only intentional scaffold files.

# Verification

- [x] From `frontend/`, run `pnpm install`.
- [x] From `frontend/`, run `pnpm lint`.
- [x] From `frontend/`, run `pnpm typecheck`.
- [x] From `frontend/`, run `pnpm test`.
- [x] From `frontend/`, run `pnpm build`.
- [x] From `frontend/`, run `pnpm dev` and verify the Vite app loads locally.
- [x] Confirm no real secrets or local-only `.env` files are tracked.

# Risks

- Network access may be required to resolve npm packages during scaffolding.
- Vite, Tailwind, and lint tooling defaults may not align perfectly with ts-standard, so config may need a small compatibility pass.
- Tailwind's current major version setup may differ from older PostCSS examples; use the installed version's documented wiring.
- Frontend API base URLs are browser-visible by design, so only public configuration belongs in `VITE_` values.
- Adding too much sample UI now could blur the line between scaffold and product feature work.
- The backend Laravel app does not exist yet, so API client behavior can only be tested with mocked responses or unit-level coverage.

# Open Questions

- Should the frontend scaffold include Pinia now, or wait until the first stateful feature needs a store?
- Should browser/e2e tooling be deferred, or should Playwright be added while the app is still tiny?
- Should the scaffold use a hand-built API client around `fetch`, or introduce Axios only if a future feature needs interceptors or cancellation ergonomics?
