# Pizza Planet

Pizza Planet is a split Laravel and Vue application.

## Contents

- [Project layout](#project-layout)
- [Get up and running](#get-up-and-running)
- [Checks](#checks)
- [Integration guides](#integration-guides)

## Project layout

- Backend code lives in `backend/`.
- Frontend code lives in `frontend/`.
- Local development uses Jetify Devbox.
- MySQL, Redis, the Laravel API, and the Vite website run through Devbox services.
- Backend setup and commands: [backend/README.md](backend/README.md)
- Frontend setup and commands: [frontend/README.md](frontend/README.md)

## Get up and running

Install Jetify Devbox, then enter the project shell from the repo root:

```sh
devbox shell
```

Check that the local toolchain is available:

```sh
devbox run preflight
```

On a fresh checkout, complete the app-specific setup in the backend and frontend guides before starting the full service set.

Start MySQL, Redis, the API, and the website:

```sh
devbox run services:start
devbox run services:check
```

Create the local MySQL database and application user:

```sh
devbox run db:setup
devbox run services:check
```

App-specific setup and checks:

- [Backend guide](backend/README.md)
- [Frontend guide](frontend/README.md)

Stop background services when you are finished:

```sh
devbox run services:stop
```

Local URLs:

- Website: http://127.0.0.1:5173/
- API: http://127.0.0.1:8000/

## Checks

Run app-specific checks using each guide:

- Backend: [backend/README.md](backend/README.md)
- Frontend: [frontend/README.md](frontend/README.md)

## Integration guides

- Backend website webhooks: [backend/WEBHOOK-README.md](backend/WEBHOOK-README.md)
