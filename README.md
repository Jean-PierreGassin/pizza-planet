# Pizza Planet

Pizza Planet is a split Laravel and Vue application.

- Backend code lives in `backend/`.
- Frontend code lives in `frontend/`.
- Local development uses Jetify Devbox.
- MySQL and Redis run through Devbox services.

## Get up and running

Install Jetify Devbox, then enter the project shell from the repo root:

```sh
devbox shell
```

Check that the local toolchain is available:

```sh
devbox run preflight
```

Start MySQL and Redis:

```sh
devbox run services:start
devbox run services:check
```

Create the local MySQL database and application user:

```sh
devbox run db:setup
devbox run services:check
```

Install backend dependencies:

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Install frontend dependencies:

```sh
cd frontend
pnpm install
```

Run the app in two terminals:

```sh
cd backend
php artisan serve
```

```sh
cd frontend
pnpm dev
```

Stop background services when you are finished:

```sh
devbox run services:stop
```

## Checks

Run backend tests and static analysis from `backend/`:

```sh
composer test
composer analyse
```

Run frontend tests and linting from `frontend/`:

```sh
pnpm test
pnpm lint
pnpm typecheck
```