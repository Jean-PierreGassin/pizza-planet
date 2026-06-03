# Pizza Planet Backend

Laravel backend application for Pizza Planet.

Use the root `README.md` first for Devbox, services, and full-repo startup. This guide covers backend-only setup and commands.

## Contents

- [Setup](#setup)
- [Checks](#checks)
- [Routes](#routes)
- [Runtime Defaults](#runtime-defaults)
- [Integration Guides](#integration-guides)

## Setup

Start the local service set from the repo root so MySQL and Redis are running:

```sh
devbox run services:start
```

Create the local database and application user from the repo root:

```sh
devbox run db:setup
```

Run the backend setup script from `backend/`:

```sh
composer run-script setup
```

That script installs Composer dependencies, creates `.env` if needed, generates the app key, runs migrations, and seeds the local demo data.

For backend-only work after setup, you can run:

```sh
composer dev
```

The backend server and queue worker also run from the repo root as part of the full Devbox service set:

```sh
devbox run services:start
```

## Checks

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
- Queue worker: Devbox service processing `webhooks` and `default`
- Redis client: Predis
- Cache: Redis

## Integration Guides

- Website webhook setup and signing: [WEBHOOK-README.md](WEBHOOK-README.md)
- API v1 routes and Postman import: [../.docs/backend/routes/API-v1.md](../.docs/backend/routes/API-v1.md)
