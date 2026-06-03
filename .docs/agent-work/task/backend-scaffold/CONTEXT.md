# Context

## Summary

Pizza Planet now has a Laravel 13 backend scaffold under `backend/`. Backend dependencies are isolated in `backend/composer.json`, `.env.example` points at the Devbox MySQL and Redis services using local placeholders, and the scaffold verifies with PHPUnit, PHPStan/Larastan, and MySQL migrations.

## Decisions

- Decision: Track this plan under `.docs/agent-work/task/backend-scaffold/`.
  - Reason: Existing agent work plans live under `.docs/agent-work/task/`.
  - Date: 2026-06-02

- Decision: Treat backend scaffolding as a backend-only task.
  - Reason: Project architecture requires frontend and backend code to remain clearly separated.
  - Date: 2026-06-02

- Decision: Recommend Laravel 13 for the scaffold.
  - Reason: The official Laravel release notes list Laravel 13 as supporting PHP 8.3 through PHP 8.5, matching the current Devbox PHP 8.5 setup.
  - Date: 2026-06-02

- Decision: Keep real local environment values out of tracked files.
  - Reason: Project safety rules prohibit committing or summarizing secrets, credentials, tokens, private keys, or local environment values.
  - Date: 2026-06-02

- Decision: Remove Laravel's generated frontend/Vite files from the backend scaffold.
  - Reason: The repo is split into separate backend and frontend roots, and frontend scaffolding is happening in parallel under `frontend/`.
  - Date: 2026-06-02

- Decision: Use Predis for Laravel Redis access.
  - Reason: The Devbox PHP runtime has `pdo_mysql` but does not load the `redis` extension, so Predis keeps Redis usable without changing the PHP package set.
  - Date: 2026-06-02

- Decision: Use Redis as the default queue connection.
  - Reason: Redis is part of the defined backend service stack and is a better queue backend default than the database for this scaffold.
  - Date: 2026-06-02

- Decision: Use `php -d memory_limit=1G vendor/bin/phpstan analyse --debug` for `composer analyse`.
  - Reason: PHPStan exhausted the default 128 MB memory limit and the non-debug invocation exited without readable diagnostics in this environment.
  - Date: 2026-06-02

## Discoveries

- Discovery: `README.md` already describes post-scaffold backend commands from `backend/`.
  - Source: `README.md`
  - Impact: The scaffold should make those documented commands true rather than changing the project shape.

- Discovery: Backend code must live under `backend/`, frontend code must live under `frontend/`, and the sides must remain separated.
  - Source: `.docs/ARCHITECTURE.md`
  - Impact: Do not create the Laravel app at the repo root.

- Discovery: Backend architecture uses Controller-Service-Repository.
  - Source: `.docs/backend/ARCHITECTURE.md`
  - Impact: Add convention directories for services, repositories, DTOs, enums, requests, jobs, events, listeners, and models without creating feature code.

- Discovery: Backend PHP code should follow PSR-12, use focused classes, and avoid unnecessary docblocks.
  - Source: `.docs/backend/CODE-QUALITY.md`
  - Impact: Generated and added files should stay conventional, typed where useful, and lightly commented.

- Discovery: Tests should use PHPUnit and Vitest, with backend scaffold verification focused on PHPUnit.
  - Source: `.docs/WRITING-TESTS.md`
  - Impact: Add `composer test` for backend PHPUnit/Laravel tests.

- Discovery: Devbox provides MySQL database settings through local placeholder values.
  - Source: `devbox.json`
  - Impact: `backend/.env.example` should use `pizza_planet` local database placeholders, while real `.env` remains untracked.

- Discovery: The current root `composer.json` only requires `jean-pierre-gassin/ai-context`.
  - Source: `composer.json`
  - Impact: Backend Laravel dependencies should live in `backend/composer.json`, not replace the root Composer file.

- Discovery: Laravel's official release policy lists Laravel 13 as released on March 17, 2026, supporting PHP 8.3 through PHP 8.5, with security fixes until March 17, 2028.
  - Source: `https://laravel.com/docs/13.x/releases`
  - Impact: Laravel 13 is compatible with the current PHP 8.5 Devbox baseline.

- Discovery: Laravel scaffolded as `laravel/laravel` v13.8.0, resolving `laravel/framework` v13.12.0.
  - Source: `composer create-project laravel/laravel`
  - Impact: The backend scaffold is on the current Laravel 13 line.

- Discovery: The sandbox blocks local MySQL/Redis socket checks, even when services are listening.
  - Source: `devbox run services:check`, `redis-cli`, and `php artisan migrate`
  - Impact: Local service checks and migrations need sandbox escalation during Codex verification.

- Discovery: `composer test` passes with 2 feature tests and 4 assertions.
  - Source: `cd backend && composer test`
  - Impact: The scaffold has a passing backend test baseline.

- Discovery: `composer analyse` passes with 0 PHPStan errors.
  - Source: `cd backend && composer analyse`
  - Impact: The scaffold has a passing backend static-analysis baseline.

## Changes in Direction

- Change: Use API-oriented backend routes instead of Laravel's generated welcome view.
  - Previous approach: Laravel generated `resources/views/welcome.blade.php` and a web route returning that view.
  - New approach: The root route and `/api/health` return JSON status responses.
  - Reason: This backend is split from the Vue frontend, so browser assets belong in `frontend/`.

## Blockers

- Blocker:
  - Impact:
  - Possible resolution:

## Notes

- Do not print or commit local `.env` contents.
- Do not use MySQL root for the Laravel app connection.
- `backend/.env` was created for verification and remains ignored.
- Devbox services were started during verification.
- The parallel frontend scaffold changes were intentionally ignored.
