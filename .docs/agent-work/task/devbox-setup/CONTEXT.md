# Context

## Summary

Pizza Planet is ready for project scaffolding, but this plan intentionally stops before installing Laravel, Vue, Tailwind, Vite, PHPUnit, Vitest, PHPStan, Larastan, StandardJS, or ts-standard.

The repository currently has separate `backend/` and `frontend/` directories with placeholder `.gitignore` files. Project documentation requires backend and frontend code to remain clearly separated.

The devbox direction is now Jetify Devbox with no Docker. The selected local stack is PHP 8.5, Composer 2 latest, Node.js LTS, pnpm, MySQL, and Redis.

## Decisions

- Decision: Keep the devbox plan under `.docs/agent-work/task/devbox-setup/`.
  - Reason: Existing project documentation lives under `.docs/`, so this keeps planning artifacts near the rest of the repo guidance.
  - Date: 2026-06-02

- Decision: Plan infrastructure and runtime readiness before framework installation.
  - Reason: The user explicitly asked for devbox planning before installing Laravel, Vue, or related tools.
  - Date: 2026-06-02

- Decision: Use Jetify Devbox for the project devbox.
  - Reason: The user selected Jetify Devbox as the required setup path.
  - Date: 2026-06-02

- Decision: Do not use Docker, Docker Compose, or Laravel Sail for local development setup.
  - Reason: The user explicitly said no Docker.
  - Date: 2026-06-02

- Decision: Use PHP 8.5, Composer 2 latest, Node.js LTS, pnpm, MySQL, and Redis.
  - Reason: The user selected these runtime and service choices after updating `AGENTS.md` with MySQL.
  - Date: 2026-06-02

- Decision: Do not include a mail capture service in the devbox.
  - Reason: The user confirmed no mail service is required.
  - Date: 2026-06-02

- Decision: Pin Node.js to `nodejs_24@latest` for the Devbox package.
  - Reason: Node.js 24 is the active LTS line as of this setup pass, and the user requested Node.js LTS.
  - Date: 2026-06-02

- Decision: Add a `db:setup` script for the local MySQL database and user.
  - Reason: The Devbox MySQL plugin starts the service, while the application still needs a least-privilege local database account matching the documented environment values.
  - Date: 2026-06-02

## Discoveries

- Discovery: The intended stack is Laravel LTS, MySQL, Vue 3, TypeScript, Tailwind CSS, Vite, PHPUnit, Vitest, PHPStan with Larastan, and StandardJS with ts-standard.
  - Source: `AGENTS.md`
  - Impact: Runtime planning must cover PHP, Composer, Node.js, pnpm, MySQL, Redis, static analysis, and test runners.

- Discovery: Frontend code must live under `frontend/`, backend code must live under `backend/`, and the two sides must remain clearly separated.
  - Source: `.docs/ARCHITECTURE.md`
  - Impact: Devbox commands and future scaffolding should not merge Laravel and Vue into one mixed root.

- Discovery: Backend architecture will use Controller-Service-Repository.
  - Source: `.docs/backend/ARCHITECTURE.md`
  - Impact: Backend scaffolding should later preserve room for service, repository, DTO, enum, job, event, listener, and model directories.

- Discovery: Frontend architecture expects app, layouts, pages, features, shared infrastructure, stores, and API layers.
  - Source: `.docs/frontend/ARCHITECTURE.md`
  - Impact: Vue scaffolding should later be shaped toward feature-based organization rather than API calls inside components.

- Discovery: Tests should use PHPUnit and Vitest with fail-first methodology.
  - Source: `.docs/WRITING-TESTS.md`
  - Impact: Devbox verification should make room for both PHP and Node test tooling.

- Discovery: `devbox` is not currently installed in this execution environment.
  - Source: `command -v devbox`
  - Impact: `devbox.lock` and live service verification cannot be generated until Devbox is installed.

- Discovery: Devbox became available and `devbox run preflight` passed.
  - Source: `devbox run preflight`
  - Impact: The toolchain resolves to PHP 8.5.6, Composer 2.9.8, Node 24.12.0, pnpm 11.1.2, MySQL 8.0.45, and Redis 8.6.3.

- Discovery: `devbox services ls` reported no services running even while MySQL and Redis health checks passed.
  - Source: `devbox services ls` and `devbox run services:check`
  - Impact: README and scripts should use `services:check` as the reliable status signal.

## Changes in Direction

- Change: Replace Docker Compose recommendation with Jetify Devbox.
  - Previous approach: Docker Compose for shared infrastructure services and local runtimes for app tooling.
  - New approach: Jetify Devbox for tools and services, with no Docker.
  - Reason: User clarified the intended devbox technology.

## Blockers

- Blocker: Laravel LTS target has not been confirmed against PHP 8.5 support.
  - Impact: Laravel scaffolding could fail or require a different PHP version if the chosen LTS does not support PHP 8.5 yet.
  - Possible resolution: Confirm Laravel LTS and Composer package compatibility before scaffolding.

- Blocker: Devbox is not installed in this execution environment.
  - Impact: `devbox.lock` cannot be generated and services cannot be started for live verification yet.
  - Possible resolution: Install Devbox, then run `devbox install`, `devbox run preflight`, and `devbox services up -b`.

## Notes

- Do not commit secrets, private keys, credentials, tokens, real `.env` values, production database dumps, or service account files.
- Frontend public environment variables must be treated as public and must not contain secrets.
- Local service accounts should use least privilege.
- Jetify docs say `devbox.json` lives in the project root, `devbox.lock` should be committed, packages can be pinned with `package@version`, scripts run via `devbox run`, and services run via `devbox services`.
