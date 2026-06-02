# Pizza Planet

Pizza Planet is a split Laravel and Vue application.

- Backend code lives in `backend/`.
- Frontend code lives in `frontend/`.
- Local development uses Jetify Devbox.
- MySQL and Redis run through Devbox services.
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

Install and run each app using its own guide:

- [Backend guide](backend/README.md)
- [Frontend guide](frontend/README.md)

Stop background services when you are finished:

```sh
devbox run services:stop
```

## Checks

Run app-specific checks using each guide:

- Backend: [backend/README.md](backend/README.md)
- Frontend: [frontend/README.md](frontend/README.md)
