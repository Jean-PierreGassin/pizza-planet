# Objective

Prepare a reproducible local devbox for the Pizza Planet stack before installing Laravel, Vue, Tailwind, Vite, test frameworks, or static analysis tools.

# Scope

This work covers the Jetify Devbox environment, runtime choices, local services, repository conventions, and verification commands needed before framework scaffolding begins.

This work does not install Laravel, create the Vue app, add application code, create database schemas, or commit secrets.

# Acceptance Criteria

- [x] Runtime versions are chosen and documented for PHP, Composer, Node.js, pnpm, MySQL, and Redis.
- [x] Backend and frontend working directories are preserved as separate roots under `backend/` and `frontend/`.
- [x] Jetify Devbox is selected and documented as the project devbox approach.
- [x] No Docker, Docker Compose, or Laravel Sail setup is required for local development.
- [x] Required local services are listed with Devbox service commands, ports, persistence expectations, and health checks.
- [x] Environment variable strategy is documented without exposing real secrets.
- [x] Preflight commands can confirm that the machine is ready before Laravel or Vue scaffolding starts.
- [x] Security constraints for secrets, least privilege, and untrusted external input are reflected in the setup plan.

# Phases

## Phase 1: Decide The Runtime Baseline

Goal:

Lock in stable tool versions that fit the intended stack and avoid avoidable churn when Laravel and Vue are added.

Tasks:

- [x] Record Laravel LTS and PHP 8.5 compatibility as a scaffold-time validation item.
- [x] Use PHP 8.5.
- [x] Use Composer 2 latest.
- [x] Use Node.js LTS.
- [x] Use pnpm for frontend dependency management.
- [x] Use MySQL for Laravel development.
- [x] Use Redis for local cache, queue, rate limit, or session behavior when the app needs it.
- [x] Exclude object storage and search services from the day-one devbox.

Selected baseline:

- PHP 8.5.
- Composer 2 latest.
- Node.js LTS.
- pnpm.
- MySQL.
- Redis.

## Phase 2: Create The Jetify Devbox Shape

Goal:

Make the project boot consistently across machines through Jetify Devbox without installing frameworks yet.

Tasks:

- [x] Add a root `devbox.json`.
- [x] Add and commit `devbox.lock` after Devbox resolves packages.
- [x] Add packages for PHP 8.5, Composer 2, Node.js LTS, pnpm, MySQL client/server tooling, Redis, and useful local CLI helpers.
- [x] Use Devbox package pinning for major versions where exact patch versions should stay flexible.
- [x] Define `devbox run` scripts for preflight checks and later backend/frontend command wrappers.
- [x] Define local services through Devbox-supported services or `process-compose.yml` if a service needs custom wiring.
- [x] Document how `backend/` and `frontend/` commands will be run from their own directories.

Project decision:

Use Jetify Devbox as the reproducible local environment. Do not use Docker, Docker Compose, or Laravel Sail for local development setup unless the team explicitly revisits that decision later.

## Phase 3: Define Local Services

Goal:

Create a clear local dependency contract before application code depends on MySQL or Redis.

Tasks:

- [x] Define MySQL service name, version, port, database name, user, and non-secret local password placeholder.
- [x] Define Redis service name, version, port, and persistence choice.
- [x] Exclude optional object storage from the day-one service set.
- [x] Add health checks so setup failures are obvious through direct client checks.

Initial service set:

- MySQL on `127.0.0.1:3306`, unless a port conflict requires a documented alternate port.
- Redis on `127.0.0.1:6379`, unless a port conflict requires a documented alternate port.

Service commands:

```sh
devbox run services:start
devbox run db:setup
devbox run services:check
devbox run services:stop
```

Validation note:

Use `devbox run services:check` as the reliable health signal. In the local validation pass, MySQL and Redis were reachable even when `devbox services ls` reported no currently running services.

## Phase 4: Establish Environment Conventions

Goal:

Make local configuration explicit without leaking credentials or coupling dev setup to one machine.

Tasks:

- [x] Defer `backend/.env.example` and `frontend/.env.example` until framework scaffolding creates the app env surfaces.
- [x] Document that real `.env` files stay untracked.
- [x] Use local-only placeholder values in Devbox config, never real credentials.
- [x] Decide not to load a root `.env` through `env_from` in the pre-scaffold Devbox setup.
- [x] Reserve frontend public environment variables for non-secret values only.
- [x] Document expected localhost URLs and local service ports before API calls are implemented.

Security notes:

- Never commit real `.env` files, tokens, private keys, service account JSON, database dumps, or production credentials.
- Treat third-party callbacks, uploads, and browser-provided data as untrusted when the app is later scaffolded.
- Use least-privilege local database users rather than connecting as a root or superuser account by default.

## Phase 5: Add Preflight Verification

Goal:

Give contributors a quick way to prove their machine is ready before framework installation.

Tasks:

- [x] Document commands that print versions for PHP, Composer, Node, pnpm, MySQL, Redis, and Devbox.
- [x] Document service startup and health-check commands using `devbox services`.
- [x] Confirm that assigned service ports work locally.
- [x] Confirm Git ignores local env files, service volumes, generated logs, and dependency directories.
- [x] Add `devbox run` scripts instead of a Makefile for repeated setup commands unless a separate task runner becomes clearly useful.

Preflight command candidates:

```sh
devbox run preflight
devbox run services:start
devbox run services:check
devbox run db:setup
devbox run services:stop
```

## Phase 6: Prepare For Framework Scaffolding

Goal:

Make the next task, installing Laravel and Vue, straightforward and low-risk.

Tasks:

- [x] Confirm `backend/` is empty except for intentional placeholder files.
- [x] Confirm `frontend/` is empty except for intentional placeholder files.
- [x] Keep top-level Composer usage separate from future backend app dependencies for now.
- [x] Document that backend and frontend commands should run independently from their own roots.
- [x] Prepare the expected command map for later scaffolding.

Future command map:

- Backend install and checks should run from `backend/`.
- Frontend install and checks should run from `frontend/`.
- Repo-level `devbox run` commands should orchestrate both sides without hiding which side failed.

# Verification

- [x] Review `.docs/ARCHITECTURE.md`, `.docs/CODE-QUALITY.md`, `.docs/WRITING-TESTS.md`, backend docs, frontend docs, and `AGENTS.md`.
- [x] Run preflight version checks through Devbox.
- [x] Start MySQL and Redis through Devbox services and verify health checks.
- [x] Confirm no secrets or real environment values are written to tracked files.
- [x] Confirm `git status --short` only shows intentional documentation or setup changes before commit.

# Risks

- PHP 8.5 may not be supported by the chosen Laravel LTS or required Composer packages yet; confirm compatibility during Laravel scaffolding.
- Devbox plugin service status reporting may be misleading; use `devbox run services:check` for health.
- A single top-level dependency manager could blur the intended backend/frontend separation.
- Example environment files can accidentally become secret-shaped if copied from real local config.
- Default service ports may collide with existing local development projects.

# Follow-Up Questions

- Which Laravel LTS target are we using, and does it support PHP 8.5 at scaffold time?
- Should MySQL and Redis data remain Devbox-managed under `.devbox/`, or should later app work introduce a named repo-ignored data directory?
