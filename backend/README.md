# Pizza Planet Backend

Laravel backend application for Pizza Planet.

For full repository setup, Devbox services, and frontend commands, use the root `README.md`.

## Local Commands

Run these from `backend/` after the root setup is complete:

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Start the backend server:

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
