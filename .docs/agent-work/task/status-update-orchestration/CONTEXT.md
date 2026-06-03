# Context

## Summary

This planning pass prepares the backend-only status update orchestration implementation for Pizza Planet. The current backend has Laravel 13, MySQL-backed order/order-item schema, fulfillment and status enums, item status event persistence, item sync event persistence, factories, and basic model architecture tests. It does not yet have controllers, requests, services, repositories, events, listeners, jobs, order-level status/sync tables, or `spatie/laravel-webhook-server`.

No implementation code has been changed as part of this planning pass.

## Decisions

- Decision: Track this work under `.docs/agent-work/task/status-update-orchestration/`.
  - Reason: Existing agent work plans use `.docs/agent-work/task/<name>/`, and this is a backend task rather than a feature visible in the frontend.
  - Date: 2026-06-03

- Decision: Keep implementation inside `backend/`.
  - Reason: Project architecture requires backend and frontend code to remain separated, and the prompt explicitly excludes frontend work.
  - Date: 2026-06-03

- Decision: Use the Controller-Service-Repository pattern for the implementation plan.
  - Reason: `.docs/backend/ARCHITECTURE.md` defines request classes for validation, thin controllers, services for business logic, and repositories for persistence.
  - Date: 2026-06-03

- Decision: Prefer domain-specific `order_status_events` and `order_sync_events` tables for order finalization.
  - Reason: The existing `order_item_sync_events` table is tied to item status events, and the prompt recommends the clearer domain-specific option unless there is a strong reason to generalize.
  - Date: 2026-06-03

- Decision: Treat webhook queueing as an after-commit side effect.
  - Reason: Queue jobs must not run before status, event, and sync records are committed.
  - Date: 2026-06-03

- Decision: Use persisted IDs only in domain events.
  - Reason: Request data should not be the source of truth for webhook payloads or delivery state.
  - Date: 2026-06-03

- Decision: Use the security-review workflow during planning.
  - Reason: The work handles webhook secrets, signed external delivery, background jobs, and untrusted API input.
  - Date: 2026-06-03

- Decision: Install `spatie/laravel-webhook-server` during Phase 1.
  - Reason: The status orchestration prompt requires Spatie for queued, signed, retried website webhooks, and it was not already installed.
  - Date: 2026-06-03

- Decision: Publish and track `backend/config/webhook-server.php`.
  - Reason: The workflow needs stable queue, header, retry, timeout, SSL verification, and tag defaults that can be reviewed and overridden through environment placeholders.
  - Date: 2026-06-03

- Decision: Keep Redis queue `after_commit` disabled globally.
  - Reason: The workflow should use explicit after-commit dispatch for status orchestration instead of changing queue behavior repo-wide.
  - Date: 2026-06-03

- Decision: Use placeholder website webhook config in tracked files.
  - Reason: The application needs config keys for the integration, but project safety rules prohibit committing real secrets or local environment values.
  - Date: 2026-06-03

- Decision: Add `backend/WEBHOOK-README.md` and link it from the root and backend READMEs.
  - Reason: Webhook setup, signing, secret generation, and receiver expectations are backend integration details that should be easy to find without bloating the setup guides.
  - Date: 2026-06-03

- Decision: Add `composer webhook:secret` as the documented local signing secret generation command.
  - Reason: It wraps the existing backend PHP runtime in a discoverable project command and produces a high-entropy 64-character hex secret without requiring extra tools.
  - Date: 2026-06-03

## Discoveries

- Discovery: The current worktree is on `dev` tracking `origin/dev`, and `.docs/agent-work/prompts/status-update-orchestration-plan.md` is already modified.
  - Source: `git status --short --branch`
  - Impact: Treat the prompt file as user-owned and avoid rewriting it during planning.

- Discovery: Backend code must live under `backend/`, frontend code under `frontend/`, and the sides must remain clearly separated.
  - Source: `.docs/ARCHITECTURE.md`
  - Impact: The orchestration implementation should not touch frontend files.

- Discovery: The backend architecture uses Controller-Service-Repository.
  - Source: `.docs/backend/ARCHITECTURE.md`
  - Impact: The plan should add requests/controllers, services, repositories, events, listeners, jobs, and models in their conventional locations.

- Discovery: PHP code should follow PSR-12, focused classes, type declarations, and minimal comments/docblocks.
  - Source: `.docs/CODE-QUALITY.md` and `.docs/backend/CODE-QUALITY.md`
  - Impact: Implementation should keep classes small and use comments only for complex transaction/concurrency behavior.

- Discovery: Backend tests should use PHPUnit, camelCase method names with a `test` prefix, and data providers for repetitive cases.
  - Source: `.docs/WRITING-TESTS.md`
  - Impact: Transition matrix coverage should use data providers and repo naming style.

- Discovery: `OrderFulfillmentType` already has `pickup` and `delivery`.
  - Source: `backend/app/Enums/OrderFulfillmentType.php`
  - Impact: Finalized order status can be selected from persisted order state without adding enum cases.

- Discovery: `OrderStatus` already has `ready_for_pickup` and `ready_for_delivery`.
  - Source: `backend/app/Enums/OrderStatus.php`
  - Impact: The prerequisite finalized order statuses are already present.

- Discovery: `OrderItemStatus` already has `pending`, `preparing`, `baking`, and `ready`.
  - Source: `backend/app/Enums/OrderItemStatus.php`
  - Impact: Transition validation can be implemented against existing enum cases.

- Discovery: `SyncEventStatus` already has `pending`, `processing`, `delivered`, and `failed`.
  - Source: `backend/app/Enums/SyncEventStatus.php`
  - Impact: Delivery ledger status values already match the prompt.

- Discovery: Existing tables cover `orders`, `order_items`, `item_status_events`, and `order_item_sync_events`.
  - Source: `backend/database/migrations/2026_06_03_000000_create_orders_table.php` through `2026_06_03_000003_create_order_item_sync_events_table.php`
  - Impact: Implementation should add order-level tables and constraints rather than recreate the existing item tables.

- Discovery: Existing item sync events store `destination_url`, `payload`, status, attempts, attempt timestamps, delivery timestamp, last error, and response status.
  - Source: `backend/database/migrations/2026_06_03_000003_create_order_item_sync_events_table.php`
  - Impact: Order-level sync events should mirror this delivery ledger shape.

- Discovery: `spatie/laravel-webhook-server` is not currently required by `backend/composer.json`.
  - Source: `backend/composer.json`
  - Impact: Dependency installation and package API verification need to happen before webhook implementation.

- Discovery: `spatie/laravel-webhook-server` installed as version `3.10.0`, with `spatie/laravel-package-tools` `1.93.1`.
  - Source: `composer require spatie/laravel-webhook-server`
  - Impact: Later webhook services should follow the installed v3.10 API.

- Discovery: `WebhookCall::create()` supports `url`, `payload`, `useSecret`, `useTimestamp`, `meta`, `withTags`, `useJob`, and `dispatch`.
  - Source: `backend/vendor/spatie/laravel-webhook-server/src/WebhookCall.php`
  - Impact: Dispatch services can provide payloads, metadata, tags, custom jobs, and timestamped signing through the package.

- Discovery: `WebhookCall::dispatch()` returns Laravel's pending dispatch object.
  - Source: `backend/vendor/spatie/laravel-webhook-server/src/WebhookCall.php`
  - Impact: Later code can call `afterCommit()` on the pending dispatch when queueing directly through Spatie.

- Discovery: Spatie webhook events carry HTTP verb, URL, payload, headers, metadata, tags, attempt number, response, error type/message, UUID, and transfer stats.
  - Source: `backend/vendor/spatie/laravel-webhook-server/src/Events/WebhookCallEvent.php`
  - Impact: Delivery ledger listeners can update item and order sync records using metadata IDs.

- Discovery: Spatie emits `WebhookCallSucceededEvent`, `WebhookCallFailedEvent`, and `FinalWebhookCallFailedEvent`.
  - Source: `backend/vendor/spatie/laravel-webhook-server/src/CallWebhookJob.php`
  - Impact: Later listeners should map success, retryable failure, and final failure to durable sync event state.

- Discovery: `CallWebhookJob` adds timestamp headers during job handling when `useTimestamp()` is enabled.
  - Source: `backend/vendor/spatie/laravel-webhook-server/src/CallWebhookJob.php`
  - Impact: Dispatch services must opt into timestamp headers for website webhooks.

- Discovery: The published webhook server config exposes queue, connection, HTTP verb, signer, signature header, timestamp header, headers, timeout, tries, backoff strategy, webhook job, SSL verification, failure exception behavior, and tags.
  - Source: `backend/config/webhook-server.php`
  - Impact: Phase 1 config can stay package-native and environment-driven without custom transport code.

- Discovery: The root README is a repository map, while the backend README owns backend-local setup and commands.
  - Source: `README.md` and `backend/README.md`
  - Impact: Add only links and a light table of contents to existing READMEs; keep webhook details in `backend/WEBHOOK-README.md`.

- Discovery: Existing tests verify enum casts, relationships, and sync event delivery-state casts.
  - Source: `backend/tests/Feature/OrderModelArchitectureTest.php` and `backend/tests/Unit/OrderStatusTypesTest.php`
  - Impact: New tests should extend this coverage rather than replacing it.

## Changes in Direction

- Change:
  - Previous approach:
  - New approach:
  - Reason:

## Blockers

- Blocker: Implementation is awaiting user review and approval of this plan.
  - Impact: No backend code should be changed yet.
  - Possible resolution: User approves the plan or requests revisions.

## Notes

- The user asked for each phase to go through planning-work to extract context and required information, and to document the implementation process.
- The plan therefore includes `Planning context to extract`, `Required decisions`, and `Implementation process to document` subsections for each phase.
- Security review considerations are included in the plan because webhook secrets, signed delivery, queues, and state-changing API input cross trust boundaries.
- Expected verification commands are `cd backend && composer install`, `php artisan migrate`, `composer test`, and `composer analyse`.
- Running dependency installation or migrations may require approval/escalation if network or local MySQL access is blocked by the sandbox.
- Phase 1 changed backend dependency/config files only; domain schema and orchestration code begin in later phases.
- Phase 1 added webhook documentation because config placeholders and secret-generation guidance belong with the dependency/config setup.
- Phase 1 verification: `composer test` passed with 9 tests and 25 assertions.
- Phase 1 verification: `composer analyse` passed with 0 PHPStan errors.
