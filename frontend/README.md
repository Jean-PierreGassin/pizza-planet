# Pizza Planet Frontend

Vue 3, TypeScript, Tailwind CSS, Vite, Vitest, and ESLint.

## Setup

Run commands from `frontend/` inside the project Devbox shell.

```sh
pnpm install
cp .env.example .env
```

Only put browser-safe values in `.env`. Values prefixed with `VITE_` are bundled into the frontend app and must not contain secrets.

Start the website from the repo root as part of the Devbox service set:

```sh
devbox run services:start
```

For frontend-only work, you can still run:

```sh
pnpm dev
```

## Checks

```sh
pnpm lint
pnpm typecheck
pnpm test
pnpm build
```

## Scripts

- `pnpm dev` starts the Vite dev server.
- `pnpm build` type-checks and builds production assets.
- `pnpm preview` serves the built app locally.
- `pnpm test` runs Vitest once.
- `pnpm test:watch` runs Vitest in watch mode.
- `pnpm lint` runs ESLint.
- `pnpm typecheck` runs vue-tsc.
