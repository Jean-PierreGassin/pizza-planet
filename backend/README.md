# Pizza Planet Backend

Laravel backend application for Pizza Planet.

For full repository setup, Devbox services, and frontend commands, use the root `README.md`.

## Contents

- [Local Commands](#local-commands)
- [Routes](#routes)
- [Runtime Defaults](#runtime-defaults)
- [Integration Guides](#integration-guides)

## Local Commands

Run these from `backend/` after the root setup is complete:

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Start the backend server from the repo root as part of the Devbox service set:

```sh
devbox run services:start
```

For backend-only work, you can still run:

```sh
composer dev
```

Run backend checks:

```sh
composer test
composer analyse
```

## Routes

The scaffold exposes these health/status routes:

- `GET /`
- `GET /api/health`
- `GET /up`

## Runtime Defaults

- Database: MySQL
- Queue: Redis
- Redis client: Predis
- Cache: Database

## Integration Guides

- Website webhook setup and signing: [WEBHOOK-README.md](WEBHOOK-README.md)
