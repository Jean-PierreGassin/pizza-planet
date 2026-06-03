# Context

## Summary

This planning pass prepares the backend-only status update orchestration implementation for Pizza Planet. The current backend has Laravel 13, MySQL-backed order/order-item schema, fulfillment and status enums, item status event persistence, item sync event persistence, factories, and basic model architecture tests. Phase 3 adds the first controller, request, service, repository, and event path for item status transitions.

Implementation is now in progress on the approved phases.

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

- Decision: Do not add domain-specific `order_status_events` or `order_sync_events` tables.
  - Reason: The existing item status events and order item sync ledger already support the current sender-side workflow, and extra order-level tables would duplicate concepts before a concrete gap exists.
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

- Decision: Do not add order-level sync or status tables in Phase 2.
  - Reason: Order finalization happens as a side effect of an item status transition, and the existing `item_status_events` plus `order_item_sync_events` tables already provide a persisted status event and durable delivery ledger for the sender-side workflow.
  - Date: 2026-06-03

- Decision: Use a singleton resource route for `order-item-status`.
  - Reason: The frontend sends the order and order item IDs in the request body, and the endpoint can still grow to support additional resource operations such as `show`.
  - Date: 2026-06-03

- Decision: Have `UpdateOrderItemStatusRequest` validate body `order_id`, `order_item_id`, and `status`, then construct `UpdateOrderItemStatusDTO`.
  - Reason: The frontend should send the IDs it is updating, missing records should fail request validation before entering the controller/service path, and the controller should pass a DTO instead of route strings and raw payload arrays.
  - Date: 2026-06-03

- Decision: Have `OrderItemRepository` construct `OrderItemStatusTransitionDTO` after locking the item row by both order ID and item ID.
  - Reason: Once persisted state is retrieved, downstream services should receive a DTO containing the locked item plus the from/to statuses instead of re-deriving transition state from model attributes, and mismatched order/item IDs should not update the wrong item.
  - Date: 2026-06-03

- Decision: Keep enum-shape validation in `UpdateOrderItemStatusRequest` and transition graph validation in `OrderItemStatusTransitionValidatorService`.
  - Reason: Request validation should verify input shape and record existence, while the domain layer owns the allowed movement from current persisted state to requested target state.
  - Date: 2026-06-03

- Decision: Throw `InvalidOrderItemStatusTransition` for rejected movement and return HTTP 422 from the controller.
  - Reason: Invalid movement is user-correctable input, and the API should report it like validation instead of as a server error.
  - Date: 2026-06-03

- Decision: Dispatch `OrderItemStatusChangedEvent` with `DB::afterCommit()`.
  - Reason: Laravel event fakes can make fluent `dispatch()->afterCommit()` return null in tests, while `DB::afterCommit()` keeps the after-commit guarantee explicit and testable.
  - Date: 2026-06-03

- Decision: Move website webhook URL lookup and payload building out of `OrderItemStatusTransitionService`.
  - Reason: Transition orchestration should not own webhook configuration or payload shape; those belong to webhook-focused services.
  - Date: 2026-06-03

- Decision: Add `composer format` and `composer format:test` with a Pint config enforcing fully multiline method arguments.
  - Reason: Long multiline method arguments should be enforced by tooling instead of relying on review comments.
  - Date: 2026-06-03

- Decision: Suffix new class names by layer type.
  - Reason: Controllers, repositories, services, events, and DTOs should follow the app convention with explicit suffixes such as `Service`, `Repository`, `Event`, and `DTO`.
  - Date: 2026-06-03

- Decision: Add `backend/WEBHOOK-README.md` and link it from the root and backend READMEs.
  - Reason: Webhook setup, signing, secret generation, and receiver expectations are backend integration details that should be easy to find without bloating the setup guides.
  - Date: 2026-06-03

- Decision: Add `composer webhook:secret` as the documented local signing secret generation command.
  - Reason: It wraps the existing backend PHP runtime in a discoverable project command and produces a high-entropy 64-character hex secret without requiring extra tools.
  - Date: 2026-06-03

- Decision: Add `OrderFinalizationService` and `OrderRepository` for parent-order finalization.
  - Reason: Finalization needs its own parent-row lock, sibling status check, persisted fulfillment-type decision, and order status update while keeping `OrderItemStatusTransitionService` focused on orchestration.
  - Date: 2026-06-03

- Decision: Have `OrderRepository::findForFinalization()` return `OrderItemStatusTransitionDTO` with the locked parent order.
  - Reason: Row-locking repository lookups should hand services typed transition state, and the existing item status transition DTO already carries the order, item, from-status, and to-status needed for finalization.
  - Date: 2026-06-03

- Decision: Finalize parent orders inside the existing item status transition transaction.
  - Reason: The item status, item status event, item sync event, sibling readiness check, and parent order status update should commit or roll back together.
  - Date: 2026-06-03

- Decision: Treat `ready_for_pickup` and `ready_for_delivery` as already-finalized statuses.
  - Reason: The workflow should not rewrite a finalized order or create duplicate finalized webhook work when a retry reaches the final-item-ready path again.
  - Date: 2026-06-03

- Decision: Defer a separate `OrderStatusFinalized` dispatch until Phase 5.
  - Reason: Phase 2 intentionally avoided order-level status/sync tables, so finalized-order webhook delivery should be designed alongside the existing sync-ledger extension rather than added as a partial event without durable delivery state.
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
  - Impact: Implementation should reuse the existing tables until a concrete finalization or delivery requirement proves extra persistence is needed.

- Discovery: Existing item sync events store `destination_url`, `payload`, status, attempts, attempt timestamps, delivery timestamp, last error, and response status.
  - Source: `backend/database/migrations/2026_06_03_000003_create_order_item_sync_events_table.php`
  - Impact: The existing ledger can carry website sync delivery state for this phase without adding duplicate order-level sync tables.

- Discovery: `Order`, `OrderItem`, `ItemStatusEvent`, and `OrderItemSyncEvent` already use enum casts, fillable attributes, factories, and relationship methods.
  - Source: `backend/app/Models`
  - Impact: Phase 2 does not need new models to support the next implementation phase.

- Discovery: Existing model architecture tests cover enum casts, delivery-state casts, and relationships.
  - Source: `backend/tests/Feature/OrderModelArchitectureTest.php`
  - Impact: Existing coverage remains sufficient for Phase 2 because no new schema/model surface is being added.

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

- Discovery: The API bootstrap renders JSON for `api/*` exception responses.
  - Source: `backend/bootstrap/app.php`
  - Impact: The transition controller can return validation-style 422 responses for rejected transitions.

- Discovery: Phase 3 added `OrderItemRepository::findForStatusTransition()` with `lockForUpdate()`.
  - Source: `backend/app/Repositories/OrderItemRepository.php`
  - Impact: Status changes now read the current item row under a database lock and return `OrderItemStatusTransitionDTO` before validating movement.

- Discovery: Phase 3 added repositories for item status events and order item sync events.
  - Source: `backend/app/Repositories/ItemStatusEventRepository.php` and `backend/app/Repositories/OrderItemSyncEventRepository.php`
  - Impact: The transition service appends status history and creates durable sync events without direct persistence details in the controller.

- Discovery: Phase 3 builds the initial item webhook payload inside `OrderItemStatusTransitionService`.
  - Source: `backend/app/Services/OrderItemWebhookPayloadBuilderService.php`
  - Impact: Webhook payload shape is separated from transition orchestration earlier than originally planned.

- Discovery: `OrderFulfillmentType` and `OrderStatus` are cast directly on the `Order` model.
  - Source: `backend/app/Models/Order.php`
  - Impact: `OrderFinalizationService` can select the final order status from persisted enum state without request input.

- Discovery: Multi-item order tests can use repeated `OrderItem::factory()->for($order)` calls.
  - Source: `backend/tests/Feature/OrderItemStatusTransitionTest.php`
  - Impact: Phase 4 coverage can verify sibling readiness through the existing API test style.

- Discovery: The current schema has no order-level finalization record or uniqueness constraint.
  - Source: `backend/database/migrations/2026_06_03_000000_create_orders_table.php` through `2026_06_03_000003_create_order_item_sync_events_table.php`
  - Impact: Phase 4 uses the locked order row plus finalized status guard for idempotency, and Phase 5 must decide how finalized-order webhook work is represented in the existing sync ledger.

## Changes in Direction

- Change: Do not add order-level sync/status tables.
  - Previous approach: Add `order_status_events` and `order_sync_events`, then briefly consider a generalized `sync_events` table.
  - New approach: Keep Phase 2 schema unchanged and use the existing `item_status_events` and `order_item_sync_events` tables for sender-side durability in this phase.
  - Reason: The existing tables already support the workflow surface needed now, and extra tables would duplicate delivery ledger concepts before the implementation proves they are necessary.

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
- Phase 2 is now a schema review and planning checkpoint rather than a schema implementation phase.
- Phase 2 intentionally leaves source schema/model files unchanged.
- Phase 2 verification after pivot: `composer test` passed with 9 tests and 25 assertions.
- Phase 2 verification after pivot: `composer analyse` passed with 0 PHPStan errors.
- Phase 3 added the item transition API, validator, service, repositories, domain event, and focused tests.
- Phase 3 verification: `composer test` passed with 27 tests and 61 assertions.
- Phase 3 verification: `composer analyse` passed with 0 PHPStan errors.
- Phase 4 added parent-order finalization inside the item transition transaction.
- Phase 4 finalization locks the parent order row before checking sibling item readiness.
- Phase 4 selects `ready_for_pickup` or `ready_for_delivery` from `orders.fulfillment_type`.
- Phase 4 split feature coverage into transition persistence, request validation, and order finalization test classes.
- Phase 4 verification: `composer test -- --filter 'OrderItemStatusTransitionTest|OrderItemStatusRequestTest|OrderFinalizationTest'` passed with 24 tests and 170 assertions.
- Phase 4 verification: `composer format` passed.
- Phase 4 verification: `composer test` passed with 52 tests and 214 assertions.
- Phase 4 verification: `composer analyse` passed with 0 PHPStan errors.
