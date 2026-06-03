# Objective

Hook the Vue frontend and Postman workflow into the Laravel API auth model so a seeded Pizza Planet user can log in, view seeded orders, and move order item statuses through the existing transition/webhook flow.

# Scope

This work includes first-party API authentication, locked-down versioned order routes, frontend API client versioning, seeded demo data, and a Postman-ready import for authenticated manual API testing.

This work does not include public website access, bearer-token auth for third-party clients, self-registration, password reset, role management, or changing the existing order status transition rules beyond routing them through the new resource API.

# Authentication Model

- Use Laravel Sanctum-style stateful SPA authentication for the first-party Vue app.
- Seed a demo user named `Mario` with email `mario@pizzaplanet.test` and password `ilovepizza`.
- Require the user to type credentials in the frontend login form.
- Require Postman users to type credentials into collection/environment variables before login.
- Store browser auth in secure HTTP-only session cookies, not frontend local storage.
- Protect all order reads and state-changing order item routes behind authentication.
- Keep backend web routes out of product use; `/` remains a local health response.

# API Shape

- `POST /api/v1/sessions`
- `GET /api/v1/session`
- `DELETE /api/v1/session`
- `GET /api/v1/orders`
- `GET /api/v1/orders/{order}`
- `PATCH /api/v1/orders/{order}/items/{item}`

The frontend API client should support a swappable API version per request or feature call, defaulting to `v1`.

# Acceptance Criteria

- [x] Guests cannot access order list, order detail, or order item status transition routes.
- [x] Guests cannot access the website as an application surface beyond the local root health response.
- [x] Mario can log in from the frontend by typing `mario@pizzaplanet.test` and `ilovepizza`.
- [x] The frontend does not prefill Mario's credentials.
- [x] Authenticated frontend requests include the browser session cookie and can load seeded orders.
- [x] Authenticated frontend users can transition order item statuses through the existing backend service.
- [x] Seed data includes funny Pizza Planet-flavored orders and items with realistic names.
- [x] Every seeded order and order item starts at the first lifecycle status so webhook behavior can be demonstrated from the beginning.
- [x] A Postman collection and environment are committed.
- [x] The Postman import supports login as Mario after the user manually types credentials.
- [x] The Postman collection can call order list, order detail, and item status transition routes after login.

# Phases

## Phase 1: Backend Auth Boundary

Goal:
Install and configure the API auth boundary before exposing resource routes.

Tasks:

- [x] Add the required Laravel auth package/configuration for stateful API session auth.
- [x] Configure CORS/session behavior for local Vite-to-Laravel API requests with credentials.
- [x] Add JSON auth endpoints for login, logout, and current user.
- [x] Make API auth middleware explicit for protected routes.
- [x] Keep the root web route available for local health checks without serving an application surface.
- [x] Add feature tests for successful login, failed login, logout, current user, and guest denial.

## Phase 2: Versioned Order API

Goal:
Expose orders through resource-shaped `/api/v1` endpoints.

Tasks:

- [x] Add `GET /api/v1/orders`.
- [x] Add `GET /api/v1/orders/{order}`.
- [x] Move the order item status transition to `PATCH /api/v1/orders/{order}/items/{item}`.
- [x] Keep controllers thin and delegate persistence/business rules to repositories and services.
- [x] Ensure route model binding or request validation prevents cross-order item transitions.
- [x] Add feature tests for authenticated reads, guest denial, missing resources, and invalid cross-order item transitions.

## Phase 3: Demo Seed Data

Goal:
Give the demo useful data immediately after login.

Tasks:

- [x] Replace the default test user seed with Mario.
- [x] Seed several orders with funny but plausible customer/order data.
- [x] Seed pizzas and other order items under those orders.
- [x] Ensure all seeded orders start at the initial order status.
- [x] Ensure all seeded order items start at the initial item status.
- [x] Add or update seed verification tests where practical.

## Phase 4: Frontend Auth And Orders

Goal:
Let users type credentials, log in, and work with versioned order API calls.

Tasks:

- [x] Extend the shared API client to support credentialed requests.
- [x] Add swappable API version handling per request or feature API module.
- [x] Add auth API calls under a feature-specific auth API module.
- [x] Add orders API calls under a feature-specific orders API module.
- [x] Build a login screen that requires typed credentials and does not prefill demo values.
- [x] Build an authenticated orders view that loads seeded orders after login.
- [x] Wire item status transition actions to the new nested route.
- [x] Add Vitest coverage for API client versioning/auth behavior and key login/order UI flows.

## Phase 5: Postman Import

Goal:
Provide a ready-to-import Postman workflow for manual API demos.

Tasks:

- [x] Add a Postman collection under a project documentation or tooling path.
- [x] Add a Postman environment with base URL, API version, email, and password variables.
- [x] Leave email/password variable values blank or clearly user-supplied; do not embed `ilovepizza` in an executable auth script.
- [x] Add a login request that uses the typed variables and stores session cookies through Postman's cookie jar.
- [x] Add authenticated order list, order detail, and item transition requests.
- [x] Document the route/import workflow under `.docs/backend/routes`.

# Verification

- [x] Run backend feature tests.
- [x] Run backend static analysis.
- [x] Run backend formatting check.
- [x] Run frontend unit tests.
- [x] Run frontend type checks.
- [x] Run frontend linting.
- [x] Manually verify the frontend login screen renders with blank credential fields on desktop and mobile.
- [x] Verify guest order API requests return unauthenticated responses through feature tests.
- [x] Validate the Postman collection/environment JSON structure.

# Security Review

- Sensitive assets: session cookies, CSRF token/cookie, seeded demo credentials, order data, and status transition operations.
- Privileged operations: viewing orders and moving item statuses.
- Trust boundaries: browser-to-API, Postman-to-API, route parameters, JSON request payloads, and webhook dispatch jobs.
- Required mitigations: authenticated middleware on all order routes, CSRF/session configuration for stateful requests, no local-storage auth tokens, no credential prefill, no public website surface, request validation, and cross-order item checks.
- Abuse cases to test: guest access, invalid login, missing CSRF/session setup, transitioning an item through the wrong order URL, and hitting web routes directly.

# Risks

- Cookie auth can fail in local development if CORS, session domain, SameSite, or credentials settings are misaligned.
- Postman cookie behavior can differ from browser cookie behavior, so the import needs explicit run instructions.
- Seed credentials are intentionally easy for demos; they must stay scoped to local/dev/demo use.
- Route reshaping may require updating existing feature tests that still call the old `PATCH /api/order-item-status` endpoint.
- Frontend state can accidentally clear auth on transient API failures if unauthenticated responses are not handled carefully.

# Decisions Confirmed Before Implementation

- Seed Mario with `mario@pizzaplanet.test`.
- Place Postman-ready import assets at the repository top level.
- Document backend routes under `.docs/backend/routes`.
- Return all orders from the order index for now.
