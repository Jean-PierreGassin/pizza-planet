# Objective

Scaffold the Pizza Planet backend application as a Laravel app under `backend/`, wired to the existing Devbox MySQL and Redis services, with PHPUnit, PHPStan/Larastan, and project architecture conventions ready for real backend work.

# Scope

This work covers creating the Laravel backend scaffold, configuring local environment examples, installing backend quality tooling, adding initial backend directory conventions, and verifying the scaffold with tests, static analysis, and a database migration pass.

This work does not implement product features, authentication flows, domain models, production deployment config, frontend scaffolding, or real secrets.

# Acceptance Criteria

- [x] `backend/` contains a working Laravel application scaffold.
- [x] The Laravel version is compatible with the Devbox PHP version before dependencies are installed.
- [x] Backend dependencies are isolated to `backend/composer.json`; the root Composer setup remains separate.
- [x] `backend/.env.example` uses safe local placeholders for MySQL and Redis without real secrets.
- [x] Laravel is configured to use MySQL for the local database connection.
- [x] Redis configuration is available for cache, queue, rate limiting, or sessions without requiring app features to use it yet.
- [x] Backend architecture directories exist for Controllers, Requests, Services, Repositories, DTOs, Enums, Events, Listeners, Jobs, and Models.
- [x] PHPUnit is installed and runnable from `backend/`.
- [x] PHPStan with Larastan is installed and runnable from `backend/`.
- [x] Backend Composer scripts expose the expected `composer test` and `composer analyse` commands.
- [x] Initial migrations run successfully against the Devbox MySQL database.
- [x] No real `.env` file, generated key, credential, token, private key, or database dump is committed.

# Phases

## Phase 1: Confirm The Scaffold Baseline

Goal:

Lock in the Laravel and PHP compatibility decision before writing framework files.

Tasks:

- [x] Confirm the current Laravel major release supports PHP 8.5.
- [x] Decide the version constraint to use for Laravel.
- [x] Confirm `backend/` only contains intentional placeholder files before scaffolding.
- [x] Confirm the root Composer dependency remains unrelated to the backend app.
- [x] Decide whether Laravel's default frontend/Vite files should be kept, removed, or minimized in the backend scaffold.

Current recommendation:

Use Laravel 13 with a `^13.0` framework constraint because Laravel's official support policy lists Laravel 13 as supporting PHP 8.3 through PHP 8.5.

## Phase 2: Create The Laravel App In `backend/`

Goal:

Generate a clean Laravel application without mixing backend code into the repo root or frontend workspace.

Tasks:

- [x] Run the Laravel project creation command into `backend/`.
- [x] Preserve or re-add `backend/.gitignore` so local env files, caches, dependencies, logs, and generated artifacts stay untracked.
- [x] Remove any temporary scaffold conflicts caused by the existing placeholder file.
- [x] Verify `backend/artisan` runs.
- [x] Confirm the Laravel application namespace and default paths are normal for a fresh app.

Implementation note:

Because `backend/` already exists, the implementation may need to remove only the placeholder `.gitignore` or scaffold into a temporary directory and move generated files into place. Do not remove unrelated user files.

## Phase 3: Wire Local Environment Defaults

Goal:

Make the scaffold work with the existing Devbox MySQL and Redis setup while keeping secrets out of version control.

Tasks:

- [x] Update `backend/.env.example` with local placeholder values matching `devbox.json`.
- [x] Set the default database connection to MySQL in the example environment.
- [x] Point MySQL host, port, database, user, and placeholder password at the Devbox local service values.
- [x] Point Redis host and port at the Devbox local service values.
- [x] Keep `backend/.env` untracked.
- [x] Generate an app key only in the local untracked `.env` during verification.
- [x] Avoid adding any production-only, remote, or personal environment values.

Security notes:

- `.env.example` may contain local placeholder values only.
- Generated `APP_KEY` belongs in local `.env`, never in tracked files.
- Database access should use the least-privilege `pizza_planet` local user, not MySQL root.

## Phase 4: Add Backend Quality Tooling

Goal:

Make the scaffold immediately checkable with the backend tools defined in the project stack.

Tasks:

- [x] Ensure PHPUnit is installed and configured through Laravel's default test setup.
- [x] Install PHPStan and Larastan as development dependencies.
- [x] Add a `phpstan.neon` or `phpstan.neon.dist` appropriate for a new Laravel app.
- [x] Add Composer scripts for `test` and `analyse`.
- [x] Keep PSR-12 expectations documented and avoid adding a formatter unless the team chooses one later.
- [x] Confirm generated code does not need docblocks to satisfy project style.

Recommended Composer scripts:

```json
{
  "scripts": {
    "test": "php artisan test",
    "analyse": "php -d memory_limit=1G vendor/bin/phpstan analyse --debug"
  }
}
```

## Phase 5: Establish Backend Architecture Directories

Goal:

Shape the fresh app toward the documented Controller-Service-Repository pattern without inventing feature code.

Tasks:

- [x] Ensure `app/Http/Controllers` and `app/Http/Requests` exist.
- [x] Add empty convention directories for `app/Services`, `app/Repositories`, `app/DTOs`, and `app/Enums`.
- [x] Preserve Laravel's generated `app/Events`, `app/Listeners`, `app/Jobs`, and `app/Models` structure if present, or add placeholders if Laravel does not generate them.
- [x] Keep placeholders minimal, using `.gitkeep` only where empty directories need to be tracked.
- [x] Do not create domain entities or sample business classes during scaffold work.

## Phase 6: Verify Database And Framework Health

Goal:

Prove the backend scaffold can boot, test, analyse, and talk to local services.

Tasks:

- [x] Start Devbox services if they are not already running.
- [x] Run `devbox run services:check`.
- [x] Run `devbox run db:setup`.
- [x] From `backend/`, install dependencies.
- [x] From `backend/`, create local `.env` from `.env.example`.
- [x] From `backend/`, run `php artisan key:generate`.
- [x] From `backend/`, run `php artisan migrate`.
- [x] From `backend/`, run `composer test`.
- [x] From `backend/`, run `composer analyse`.
- [x] Confirm `git status --short` shows only intentional scaffold files and no secrets.

# Verification

- [x] `devbox run preflight`
- [x] `devbox run services:check`
- [x] `devbox run db:setup`
- [x] `cd backend && composer install`
- [x] `cd backend && cp .env.example .env`
- [x] `cd backend && php artisan key:generate`
- [x] `cd backend && php artisan migrate`
- [x] `cd backend && composer test`
- [x] `cd backend && composer analyse`
- [x] `git status --short`

# Risks

- Laravel's installer and Composer needed network approval in the sandboxed Codex environment.
- Laravel generated frontend/Vite assets by default; they were removed from the backend scaffold to preserve the split app boundary.
- PHPStan needed an explicit memory limit and `--debug` invocation in this environment to avoid silent non-zero exits.
- Running migrations against local MySQL required sandbox escalation for localhost database access.
- Generated `.env` and `APP_KEY` files must remain untracked.

# Open Questions

- None.
