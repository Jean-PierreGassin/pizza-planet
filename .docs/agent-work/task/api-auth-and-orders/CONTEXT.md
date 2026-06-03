# Context

## Summary

The goal is to add authenticated, API-only access to seeded orders for a Pizza Planet demo. Users must type Mario's credentials, then use the frontend or a Postman import to call versioned order routes and move order item statuses through the existing transition/webhook behavior.

## Decisions

- Decision: Use a first-party session/cookie authentication model for the Vue frontend.
  - Reason: The frontend is the first-party application and should not store bearer tokens in browser-accessible storage.
  - Date: 2026-06-03

- Decision: Seed the demo user as Mario with password `ilovepizza`.
  - Reason: The demo needs a memorable seeded account for local/API testing.
  - Date: 2026-06-03

- Decision: Require users to type credentials in both the frontend and Postman flow.
  - Reason: The demo should avoid credential autofill shortcuts and make the auth step explicit.
  - Date: 2026-06-03

- Decision: Use versioned resource routes under `/api/v1`.
  - Reason: The API should be resource-based and frontend versioning should be swappable per request.
  - Date: 2026-06-03

- Decision: Keep the application API-only and lock down orders behind login.
  - Reason: Nobody should see orders except logged-in users, and the website surface is not part of the product beyond the local root health response.
  - Date: 2026-06-03

- Decision: Keep Mario's email as `mario@pizzaplanet.test`.
  - Reason: The address is clearly demo-only and easy to type.
  - Date: 2026-06-03

- Decision: Place Postman-ready import assets at the repository top level and route docs under `.docs/backend/routes`.
  - Reason: The import should be easy to find, while route documentation belongs with backend docs.
  - Date: 2026-06-03

- Decision: Return all orders from the order index for now.
  - Reason: The demo should display all seeded orders without filtering.
  - Date: 2026-06-03

## Discoveries

- Discovery: The existing API route protects the order item status transition with `Route::middleware('auth')`.
  - Source: `backend/routes/api.php`
  - Impact: The implementation should make the API guard explicit and move the transition behind `/api/v1/orders/{order}/items/{item}`.

- Discovery: The existing frontend API client already centralizes fetch behavior.
  - Source: `frontend/src/shared/api/client.ts`
  - Impact: Credential handling and per-request API versioning can be added at the shared client boundary.

- Discovery: The backend already has order, order item, status event, and webhook sync models/services.
  - Source: `backend/app/Models`, `backend/app/Services`, and existing feature tests.
  - Impact: The plan should route through existing domain services instead of reinventing status transition logic.

- Discovery: Project docs require frontend/backend separation and frontend API calls outside Vue components.
  - Source: `.docs/ARCHITECTURE.md` and `.docs/frontend/ARCHITECTURE.md`
  - Impact: Auth and order API modules should live under feature/shared API folders, not directly in page components.

- Discovery: Laravel 13 exposes Sanctum's SPA middleware through `$middleware->statefulApi()`.
  - Source: `backend/vendor/laravel/framework/src/Illuminate/Foundation/Configuration/Middleware.php`
  - Impact: The implementation can use framework-supported stateful API sessions without custom middleware.

- Discovery: Browser verification with only the frontend server running should keep the initial session probe quiet.
  - Source: Local browser check on `http://127.0.0.1:5174/`
  - Impact: The login screen does not show an API error until the user submits credentials or performs an authenticated action.

## Changes in Direction

- Change: Order item transition route should become nested and versioned.
  - Previous approach: `PATCH /api/order-item-status`
  - New approach: `PATCH /api/v1/orders/{order}/items/{item}`
  - Reason: The API should be resource-based and versioned.

## Blockers

- Blocker: None currently.
  - Impact: Planning can proceed to implementation when approved.
  - Possible resolution: Continue with the confirmed decisions above.

## Notes

- The Postman import should support authentication as Mario after the user types credentials into variables.
- Seeded orders/items should all begin at the initial lifecycle state so webhook behavior can be demonstrated from the start.
- Do not expose or summarize local environment values while implementing auth/session configuration.
- Implemented Postman assets are top-level `pizza-planet.postman_collection.json` and `pizza-planet.postman_environment.json`.
- Route documentation lives at `.docs/backend/routes/API-v1.md`.
- Verification completed with backend tests, PHPStan, Pint check, frontend Vitest, frontend typecheck, frontend lint, Postman JSON validation, and browser desktop/mobile login-screen checks.
- Follow-up fixes removed the pre-auth current-session probe, restored `/` for local health checks, changed auth to session resource routes, split the frontend page into feature components, moved seed setup into factory states, and added CSRF-cookie CORS preflight coverage.
