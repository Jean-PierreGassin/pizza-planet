# Objective

Implement a reliable backend-only status update orchestration flow for order items. A controlled item status transition should persist the new status, record audit history, create durable sync events, queue signed website webhooks after commit, track delivery state, and finalize the parent order exactly once when all items are ready.

# Scope

This work covers backend schema additions, transition validation, transactional status mutation, order finalization, webhook dispatch through `spatie/laravel-webhook-server`, delivery ledger updates, API entry points, and PHPUnit/PHPStan verification.

This work does not include frontend updates, website receiver implementation, sender-side per-item sequencing, customer/payment payload expansion, production secret setup, or destructive data/history changes.

# Acceptance Criteria

- [ ] Item statuses can only move through `pending -> preparing -> baking -> ready`.
- [ ] Skipped, backwards, duplicate, and terminal item transitions are rejected before persistence.
- [ ] Item status changes are made through a single service path that updates `order_items`, appends `item_status_events`, and creates `order_item_sync_events` in one transaction.
- [ ] Item and order finalized webhook jobs are queued only after the surrounding database transaction commits.
- [ ] Website webhook calls are signed, timestamped, tagged, and configured without exposing secrets.
- [ ] Webhook delivery attempts, success, retry failure, and final failure update durable sync event state.
- [ ] Pickup orders finalize to `ready_for_pickup` and delivery orders finalize to `ready_for_delivery` when all items are ready.
- [ ] Parent order finalization is protected by row locks plus uniqueness constraints and cannot create duplicate finalized events under retries or concurrency.
- [ ] Order finalization reuses the existing item status event and item sync ledger unless implementation proves extra persistence is required.
- [ ] PHPUnit coverage verifies allowed/rejected transitions, rollback behavior, event dispatch, webhook queueing, delivery ledger updates, finalization rules, and duplicate prevention.
- [ ] Backend webhook setup is documented in `backend/WEBHOOK-README.md` and linked from the root and backend READMEs.
- [ ] `composer test` and `composer analyse` pass from `backend/`.
- [ ] No secrets, real webhook URLs, credentials, tokens, private keys, or local environment values are printed, summarized, or committed.

# Phases

## Phase 1: Baseline And Dependency Planning

Goal:

Confirm the current backend baseline, add the missing webhook package intentionally, and establish safe configuration boundaries before domain code is added.

Planning context to extract:

- [x] Re-read `.docs/ARCHITECTURE.md`, `.docs/CODE-QUALITY.md`, `.docs/backend/ARCHITECTURE.md`, `.docs/backend/CODE-QUALITY.md`, and `.docs/WRITING-TESTS.md`.
- [x] Confirm current branch and worktree state so user-owned edits are not overwritten.
- [x] Inspect `backend/composer.json`, `backend/config/services.php`, `backend/config/queue.php`, and `.env.example`.
- [x] Verify whether `spatie/laravel-webhook-server` is already installed before adding it.
- [x] Review package docs/API in the installed vendor code after dependency installation, especially event classes, job extension points, metadata, tags, and after-commit behavior.

Required decisions:

- [x] Add `spatie/laravel-webhook-server` to `backend/composer.json`.
- [x] Store only placeholder website webhook config in tracked config files, using env names such as `WEBSITE_WEBHOOK_URL` and `WEBSITE_WEBHOOK_SECRET`.
- [x] Add a backend webhook README that explains why webhooks, HMAC signing, and Spatie are used.
- [x] Link the backend webhook README from both the root README and backend README.
- [x] Include a local command for generating a webhook signing secret without committing the generated value.
- [x] Prefer explicit after-commit dispatch in the workflow rather than changing Redis queue behavior globally.
- [x] Keep webhook secrets out of logs, tests, plan notes, and final summaries.

Implementation process to document:

- [x] Record dependency version and package API discoveries in `CONTEXT.md`.
- [x] Record any config publication or manual config file choice in `CONTEXT.md`.
- [x] Record webhook documentation placement and secret-generation guidance in `CONTEXT.md`.
- [x] Record verification commands run after dependency/config changes.

## Phase 2: Schema And Model Foundations

Goal:

Confirm whether the existing order, order item, item status event, and item sync event schema can support the orchestration workflow without adding extra tables.

Planning context to extract:

- [x] Inspect existing migrations for `orders`, `order_items`, `item_status_events`, and `order_item_sync_events`.
- [x] Inspect `Order`, `OrderItem`, `ItemStatusEvent`, and `OrderItemSyncEvent` relationships and casts.
- [x] Inspect existing factories and architecture tests before extending them.

Required decisions:

- [x] Do not add `order_status_events`, `order_sync_events`, or a generalized `sync_events` table in Phase 2.
- [x] Use the existing `item_status_events` record created by the final item transition as the durable source for item status and parent-order finalization side effects.
- [x] Use the existing `order_item_sync_events` delivery ledger for queued website sync work in this phase.
- [x] Defer any schema expansion until implementation proves the existing tables cannot carry a required behavior safely.
- [x] Keep payloads minimal and avoid storing customer, payment, credential, or environment data.

Implementation process to document:

- [x] Document why no Phase 2 schema additions are needed.
- [x] Document the discarded order-level table approach as a change in direction.
- [x] Document that existing model/factory/test coverage remains sufficient for this phase.

## Phase 3: Transition Domain Layer

Goal:

Create the controlled status transition path using repositories, a transition validator, and a transaction-owned service.

Planning context to extract:

- [x] Inspect local conventions for request validation, controllers, services, repositories, and route registration.
- [x] Inspect enum cases and current model fillable/cast behavior.
- [x] Identify the exception/response style Laravel 13 uses locally for validation and domain rejection.

Required decisions:

- [x] Place transition graph rules in `OrderItemStatusTransitionValidatorService`, not the request class.
- [x] Use repositories for row-locking lookups and persistence appends.
- [x] Lock and reload the order item before checking the current status.
- [x] Dispatch `OrderItemStatusChangedEvent` after commit with persisted IDs only.
- [x] Keep `OrderItemStatusController` thin and return a stable JSON response.
- [x] Use request and repository DTOs so route payloads and locked transition state are not passed around as raw values.
- [x] Keep webhook URL and payload construction in webhook-focused services.
- [x] Add a formatter check that enforces fully multiline method arguments.
- [x] Suffix new controller, repository, service, event, and DTO classes by layer type.

Implementation process to document:

- [x] Document route shape, request payload shape, and response shape.
- [x] Document the chosen domain exception and HTTP status for invalid transitions.
- [x] Document repository methods added for locks, updates, and status event/sync event creation.

## Phase 4: Order Finalization Layer

Goal:

Finalize parent orders exactly once when every item is ready, using persisted fulfillment type and database-backed duplicate protection.

Planning context to extract:

- [x] Inspect how `OrderFulfillmentType` and `OrderStatus` are cast and stored.
- [x] Inspect sibling item query patterns and factory setup for multi-item order tests.
- [x] Identify MySQL constraint behavior for duplicate finalization attempts.

Required decisions:

- [x] Lock the parent order before checking sibling item statuses.
- [x] Run finalization inside the item transition transaction.
- [x] Choose final status from `orders.fulfillment_type`, never request input.
- [x] Treat `ready_for_pickup` and `ready_for_delivery` as terminal finalized statuses for this workflow.
- [x] Defer finalized-order webhook dispatch until Phase 5, when the existing sync ledger can be extended deliberately for that event type.

Implementation process to document:

- [x] Document how duplicate finalization is handled without creating duplicate webhook work.
- [x] Document pickup versus delivery finalization behavior.
- [x] Document concurrency assumptions and any limits of local test coverage.

## Phase 5: Webhook Payloads, Jobs, And Delivery Ledger

Goal:

Queue signed item and finalized-order webhooks through Spatie jobs and keep business-level delivery state accurate.

Planning context to extract:

- [ ] Inspect installed Spatie job base class, webhook call builder, metadata support, and event names.
- [ ] Inspect Laravel unique job APIs available in the installed Laravel version.
- [ ] Inspect Redis queue configuration and cache store settings relevant to unique job locks.

Required decisions:

- [ ] Build item payloads from `orders`, `order_items`, `item_status_events`, and `order_item_sync_events`.
- [ ] Build order finalized payloads from `orders`, `item_status_events`, and `order_item_sync_events`.
- [ ] Use `WebhookEventType` values `order_item.status_updated` and `order.status_finalized`.
- [ ] Use separate job classes for item and order finalized webhooks if unique IDs or attempt hooks need different metadata.
- [ ] Increment attempts at send attempt start, not in failure listeners.
- [ ] Update delivered, failed-attempt, and final-failure state from Spatie webhook events using metadata IDs.
- [ ] Fail safely when webhook URL or signing secret is missing, without printing secrets.

Implementation process to document:

- [ ] Document payload field lists and intentionally excluded fields.
- [ ] Document unique job key choices.
- [ ] Document Spatie event-to-listener mapping and sync event state transitions.
- [ ] Document any retry/backoff defaults accepted or overridden.

## Phase 6: End-To-End API And Verification

Goal:

Wire the workflow into the API and prove the orchestration behaves correctly under happy paths, bad transitions, rollback, retries, and finalization.

Planning context to extract:

- [ ] Inspect existing PHPUnit test style and naming before adding tests.
- [ ] Identify tests that should be unit tests versus feature tests.
- [ ] Confirm local verification commands and whether Devbox services are required.

Required decisions:

- [ ] Use PHPUnit method names with a `test` prefix and camelCase.
- [ ] Use data providers for repeated transition cases.
- [ ] Prefer focused tests around business logic instead of testing framework internals.
- [ ] Use fakes or package-supported test hooks for webhook dispatch assertions where possible.
- [ ] Run `composer test` and `composer analyse` from `backend/` before review.

Implementation process to document:

- [ ] Record each verification command and result in `CONTEXT.md`.
- [ ] Record any tests skipped or constrained by local concurrency limits.
- [ ] Record final touched files and any follow-up work left outside scope.

# Verification

- [x] `cd backend && composer install`
- [ ] `cd backend && php artisan migrate`
- [x] `cd backend && composer test`
- [x] `cd backend && composer analyse`
- [x] Targeted PHPUnit tests for `OrderItemStatusTransitionValidatorService`
- [x] Targeted PHPUnit feature tests for item transition persistence and rollback behavior
- [x] Targeted PHPUnit feature tests for order finalization and duplicate prevention
- [ ] Targeted PHPUnit tests for webhook payload builders and dispatch services
- [ ] Targeted PHPUnit tests for webhook job uniqueness and delivery ledger listeners
- [x] `git status --short`

# Risks

- Dispatching before commit can queue jobs that cannot read the persisted records they need.
- Direct model updates outside the transition service can bypass validation and sync event creation.
- Missing order-level uniqueness constraints can allow duplicate finalized webhooks under concurrent item updates.
- Attempt counts can drift if incremented both in jobs and failure listeners.
- Laravel unique jobs need a shared cache store to protect multi-worker or multi-host deployments.
- Missing or malformed webhook config must fail closed without leaking secrets.
- Spatie webhook event APIs may differ from the prompt examples, so implementation must follow the installed package version.
- Receiver-side replay, stale-event protection, and status sanity checks remain out of scope for this backend phase.

# Security Review

- Sensitive assets: website webhook signing secret, destination URL, queue payloads, delivery error text, order references, and persisted status history.
- User-controlled input: requested order ID, order item ID, and item status.
- Trust boundaries: API request to backend, backend transaction to queue, queue worker to external website webhook endpoint, Spatie webhook events back to application listeners.
- Required mitigations: enum validation at the request boundary, transition validation in the service layer, persisted IDs in events/jobs, minimal payloads, HMAC signing, timestamp headers, HTTPS URL expectation outside local development, no secret logging, and database uniqueness for finalization idempotency.
- Verification path: tests for rejected transitions, missing config, payload contents, after-commit dispatch, metadata-based ledger updates, and duplicate finalization prevention.

# Open Questions

- Should missing website webhook config reject the status update, or should it persist the status and mark the sync event failed without queueing? Current recommendation: reject before persistence if the integration is required for reliable sync.
- Should item-level sync events receive an additional uniqueness constraint, and if so should it be keyed by `item_status_event_id` only or by denormalized item/status fields?
- Should webhook unique job keys use business keys like `order_item_id:to_status` and `order_id:final_status`, or stricter sync event IDs? Current recommendation: use sync event IDs if replaying the same transition event matters more than suppressing all duplicate active jobs for the same status.
- Is authentication/authorization for the POS status update endpoint in scope for this phase, or will it be handled by a separate backend auth plan?
