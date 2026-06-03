# Pizza Planet

Pizza Planet is a split Laravel and Vue application.

## Contents

- [Project layout](#project-layout)
- [Get up and running](#get-up-and-running)
- [Demo login](#demo-login)
- [Checks](#checks)
- [Further reading](#further-reading)

## Project layout

- Backend code lives in `backend/`.
- Frontend code lives in `frontend/`.
- Local development uses Jetify Devbox.
- MySQL, Redis, the Laravel API, and the Vite website run through Devbox services.
- Backend setup and commands: [backend/README.md](backend/README.md)
- Frontend setup and commands: [frontend/README.md](frontend/README.md)

## Get up and running

Install Jetify Devbox first. Then enter the project shell from the repo root:

```sh
devbox shell
```

Check the toolchain:

```sh
devbox run preflight
```

On a fresh checkout, complete the app-specific setup before checking the full service set. The backend guide starts the local services as part of database setup.

- Backend setup: [backend/README.md](backend/README.md)
- Frontend setup: [frontend/README.md](frontend/README.md)

Make sure MySQL, Redis, the API, the queue worker, and the website are running from the repo root:

```sh
devbox run services:start
```

Check the full service set:

```sh
devbox run services:check
```

Stop background services when you are finished:

```sh
devbox run services:stop
```

Local URLs:

- Website: http://127.0.0.1:5173/
- API: http://127.0.0.1:8000/
- Queue worker: runs through Devbox services for Redis queues `webhooks` and `default`.

## Demo login

The local seed data creates a demo crew user:

- Email: `mario@pizzaplanet.test`
- Password: `ilovepizza`

Type those credentials into the frontend login form. The form intentionally does not prefill them.

The seeded demo orders and order items are created when the backend seeders run. API route details and the Postman import flow are documented in [.docs/backend/routes/API-v1.md](.docs/backend/routes/API-v1.md).

## Checks

Run app-specific checks from each app directory:

- Backend: [backend/README.md](backend/README.md)
- Frontend: [frontend/README.md](frontend/README.md)

## Further reading

- Backend architecture: [.docs/backend/ARCHITECTURE.md](.docs/backend/ARCHITECTURE.md)
- Frontend architecture: [.docs/frontend/ARCHITECTURE.md](.docs/frontend/ARCHITECTURE.md)
- Testing conventions: [.docs/WRITING-TESTS.md](.docs/WRITING-TESTS.md)
- Backend website webhooks: [backend/WEBHOOK-README.md](backend/WEBHOOK-README.md)
- API v1 routes and Postman import: [.docs/backend/routes/API-v1.md](.docs/backend/routes/API-v1.md)
