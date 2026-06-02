# Pizza Planet Frontend

Vue 3, TypeScript, Tailwind CSS, Vite, Vitest, and StandardJS with ts-standard.

## Setup

Run commands from `frontend/` inside the project Devbox shell.

```sh
pnpm install
cp .env.example .env
pnpm dev
```

Only put browser-safe values in `.env`. Values prefixed with `VITE_` are bundled into the frontend app and must not contain secrets.

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
- `pnpm lint` runs ts-standard.
- `pnpm typecheck` runs vue-tsc.
