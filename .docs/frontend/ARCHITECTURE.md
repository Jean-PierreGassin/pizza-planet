# Architecture

- Place shared API infrastructure under `frontend/src/shared/api`
- Place feature-specific API calls under `frontend/src/features/<feature>/api`
- Do not place API request logic directly inside Vue components
- Frontend/backend communication must happen through the Laravel API contract

## Structure

```txt
frontend/
├── src/
│   ├── app/
│   │   ├── App.vue
│   │   ├── main.ts
│   │   ├── router/
│   │   └── providers/
│   │
│   ├── assets/
│   │   ├── images/
│   │   └── styles/
│   │
│   ├── layouts/
│   │   └── *.vue
│   │
│   ├── pages/
│   │   └── *Page.vue
│   │
│   ├── features/
│   │   └── <feature>/
│   │       ├── components/
│   │       ├── composables/
│   │       ├── stores/
│   │       ├── api/
│   │       ├── types/
│   │       └── routes.ts
│   │
│   ├── shared/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── api/
│   │   ├── utils/
│   │   ├── types/
│   │   └── constants/
│   │
│   └── stores/
│       └── *.store.ts
│
├── public/
├── tests/
├── package.json
├── tsconfig.json
├── vite.config.ts
└── README.md
```