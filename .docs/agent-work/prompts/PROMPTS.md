# Pizza Planet Prompt Portfolio

## 1. Project Operating System And Agent Workflow

> Please set up the working guidelines for this project before we start building features. I want clear architecture docs, code quality standards, test-writing conventions, and a repeatable PR workflow so future work follows the same engineering expectations.

## 2. Reproducible Local Environment

> Let's use Jetify Devbox for the local environment, not Docker. The stack should be PHP 8.5, Composer 2, Node.js LTS, pnpm, MySQL, and Redis. Add scripts for preflight checks, service startup, health checks, database setup, and local development, and make sure no secrets or local environment values are committed.

## 3. Split App Scaffolding

> Scaffold this as a split application. The Laravel backend should live under `backend/`, and the Vue 3 + TypeScript + Tailwind + Vite frontend should live under `frontend/`. Keep dependencies, environment examples, tests, and setup instructions separated by app so the repo stays clean.

## 4. Backend Quality Baseline

> Configure the Laravel backend so it is ready for real application work. It should use MySQL, Redis queues, PHPUnit, PHPStan/Larastan, safe local `.env.example` values, Composer scripts for testing and analysis, and the project architecture directories for controllers, requests, services, repositories, DTOs, enums, events, listeners, jobs, and models.

## 5. Frontend Quality Baseline

> Build the frontend scaffold with Vue Router, Tailwind, Vitest, Vue Test Utils, ts-standard, typechecking, and production build scripts. Add a typed API client boundary under the shared frontend code, and only expose public browser-safe configuration through `VITE_` environment variables.

## 6. Order Domain Architecture

> Design the core order-tracking architecture for Pizza Planet. I need orders, order items, item-level status tracking, status history, fulfillment type, order status history, and durable webhook sync events. Use enum-backed statuses, proper Eloquent relationships, factories, and focused model tests.

## 7. Incremental Order Model Delivery

> Implement the order model layer in small reviewable steps instead of one large change. Start with status enums, then add the database foundation, Eloquent models, casts and relationships, factory support, fulfillment type, and model contract tests.

## 8. Reliable Status Transition Orchestration

> Add a controlled item status transition flow. Order items should only move through `pending -> preparing -> baking -> ready`. The backend should reject skipped transitions, backwards transitions, duplicate transitions, and terminal transitions before anything is persisted.

## 9. Transactional Status Updates

> Make item status updates go through one backend service path. The service should lock the relevant rows, validate the requested movement, update the item status, append status history, create webhook sync records, and only dispatch side effects after the database transaction commits.

## 10. Order Finalization

> When every item in an order is ready, finalize the parent order exactly once. Use the persisted fulfillment type to choose the final status: pickup orders become `ready_for_pickup`, and delivery orders become `ready_for_delivery`. Protect the flow with row locks and duplicate-prevention rules.

## 11. Signed Webhook Delivery

> Use `spatie/laravel-webhook-server` for outbound website webhooks instead of hand-rolling HTTP delivery. Webhooks should be queued, signed, timestamped, and tagged. Keep payloads minimal, never expose secrets, and track attempts, success, retry failure, and final failure in a durable sync ledger.

## 12. Webhook Documentation And Safety

> Add backend webhook documentation that explains the configuration, signing secret generation, receiver expectations, and local setup. Make it clear that real webhook URLs, secrets, credentials, tokens, and private keys must not be committed, printed, or summarized.

## 13. Authenticated API And Demo Workflow

> Add first-party session and cookie authentication for the Vue app. Seed a demo user named Mario, require the user to type credentials, protect all order routes, expose versioned `/api/v1` session and order endpoints, and make sure guests cannot access order data.

## 14. Demo Frontend For Orders

> Build the frontend demo flow so a user can log in, load seeded orders, and move order items through the backend transition and webhook workflow. Keep API calls out of Vue components, support swappable API versions, and make the order dashboard useful for demonstrating the system.

## 15. Postman Manual Testing Workflow

> Add a Postman collection and environment for manual API testing. It should support logging in, listing orders, viewing order details, and transitioning item statuses. Leave credential values user-supplied instead of embedding the demo password in executable scripts.

## 16. Devbox App Services And Queue Worker

> Update the Devbox setup so local development can run the backend server, frontend server, and queue worker together. The goal is to exercise API requests, the Vue UI, Redis queues, and webhook jobs in the same local workflow.

## 17. Setup Docs And Redis Defaults

> Align the setup docs with how the repo actually works now. Make backend setup reset the database when appropriate, make Redis-backed cache and queue defaults explicit, and keep root/backend/frontend documentation from duplicating the same instructions.

## 18. Webhook Retry And Dashboard Polish

> Tune webhook delivery retries with a custom backoff strategy and add unit coverage for the retry timing. Improve the orders dashboard so active and ready orders are grouped clearly, item status progress is visible, and orders reload after transitions so finalized state appears immediately.
