# Real-Time Status Update Orchestration Prompt

Use this prompt to guide the implementation of reliable real-time status updates for order items in the Pizza Planet backend.

This is an implementation prompt, not the phase planning document. The detailed `PLAN.md` and `CONTEXT.md` files should be created later for the individual phase work.

## Goal

Implement a reliable real-time status update flow for order items.

When an order item status changes, the backend should:

```text
Persist the new item status.
Record the status transition.
Create a durable sync event.
Queue a signed webhook to the website.
Track delivery state in the database.
Finalize the parent order when all items are ready.
Queue a signed webhook for the finalized order status.
```

The sender should guarantee valid status movement, durable state, safe queue dispatch, and reliable webhook attempts. The receiving website is responsible for receiver-side duplicate handling, stale-event handling, and applying only status changes that make sense for its current state.

## Architecture Fit

Keep all implementation inside `backend/`.

Follow the backend architecture:

```text
Controllers coordinate requests.
Request classes validate input.
Services own business logic.
Repositories own persistence details.
Events announce committed domain changes.
Listeners connect domain events to side effects.
Jobs perform queued work.
Models represent persisted state.
```

Use the current backend stack:

```text
Laravel
MySQL
Redis queues
PHPUnit
PHPStan/Larastan
spatie/laravel-webhook-server
```

Do not add frontend work for this phase.

This workflow depends on orders having a persisted fulfillment type. That prerequisite is handled outside this prompt, but the status orchestration implementation should expect:

```text
orders.fulfillment_type
OrderFulfillmentType
```

Expected fulfillment values:

```text
pickup
delivery
```

The workflow also needs specific finalized order statuses.

Expected finalized `OrderStatus` values:

```text
ready_for_pickup
ready_for_delivery
```

Use enum values rather than display labels. UI copy can render those values as "Ready for pickup" or "Ready for delivery" later.

Use `orders.fulfillment_type` to choose the finalized order status.

## Status Transition Rules

Use this status flow:

```text
pending -> preparing -> baking -> ready
```

Allow only the next direct transition:

```text
pending -> preparing
preparing -> baking
baking -> ready
```

Reject skipped, backwards, duplicate, or terminal transitions:

```text
pending -> baking
pending -> ready
preparing -> ready
baking -> pending
ready -> baking
ready -> preparing
ready -> ready
```

The status transition layer must be the only application path used to change an order item's status. This keeps validation, persistence, event creation, and webhook sync preparation in one predictable place.

Do not add sender-side per-item sequencing for this phase. Valid sender-side transitions already prevent impossible status movement. Receiver-side ordering and stale-event handling are expected to be handled by the receiving website.

## Order Finalization Rules

When an order item transitions to `ready`, check whether every item in the parent order is now `ready`.

If all order items are ready, finalize the parent order inside the same transaction:

```text
pickup order   -> orders.status = ready_for_pickup
delivery order -> orders.status = ready_for_delivery
```

The final order status should be selected from persisted order state, not from request input. Use `orders.fulfillment_type` to determine whether the order should become `ready_for_pickup` or `ready_for_delivery`.

Only finalize once. If the order is already in a finalized status, do not create duplicate order status events or duplicate order finalized sync events.

Use both application-level locking and database constraints to protect finalization:

```text
Lock the parent order row before checking sibling item statuses.
Check all sibling order items inside the same transaction.
Create the order finalized status/sync records inside the same transaction.
Add a unique constraint that makes duplicate finalized order events impossible.
```

Suggested constraints:

```text
order_status_events unique(order_id, to_status)
order_sync_events unique(order_id, event_type, to_status)
```

If the phase plan chooses a generalized sync event table instead of `order_sync_events`, add the equivalent uniqueness rule there.

The finalized order status must also send a webhook to the website.

Use a separate order-level event type:

```text
order.status_finalized
```

Keep this separate from the item-level event type:

```text
order_item.status_updated
```

Because the current `order_item_sync_events` table is tied to `item_status_events`, do not force order-level webhooks into that table without an explicit schema decision. The phase plan should choose one of these approaches:

```text
Add order_status_events and order_sync_events for order-level status changes.
Generalize sync events so one table can represent item-level and order-level webhook deliveries.
```

Prefer the clearer domain-specific option unless the phase plan identifies a strong reason to generalize.

## Transaction Flow

Apply status changes inside one database transaction:

```text
Begin transaction
  Lock order item row
  Read latest committed item status
  Validate requested next status
  Update order_items.status
  Create item_status_events record
  Create order_item_sync_events record
  If item is now ready and all order items are ready:
    Lock parent order row
    Check sibling item statuses inside the same transaction
    Update orders.status to ready_for_pickup or ready_for_delivery
    Create order status event record
    Create order finalized sync event record
Commit transaction
Dispatch OrderItemStatusChanged after commit
Dispatch OrderStatusFinalized after commit when the order was finalized
```

Row locking matters because two parallel requests should not both read the same current status and create conflicting status transitions. The item row lock protects item status changes. The parent order row lock serializes finalization decisions for the same order.

The webhook queue dispatch must happen after commit. Prefer explicit after-commit dispatch for this workflow rather than changing the global Redis queue connection unless the phase plan chooses that as an intentional repo-wide config change.

Preferred shape:

```php
OrderItemStatusChanged::dispatch(
    itemStatusEventId: $itemStatusEvent->id,
    orderItemSyncEventId: $syncEvent->id,
)->afterCommit();
```

If event dispatch cannot use that exact form, use an equivalent explicit `DB::afterCommit()` callback.

## Expected Implementation Structure

Create the structure below unless the phase plan finds a better local fit.

### Controllers and Requests

```text
app/Http/Controllers/OrderItemStatusController.php
app/Http/Requests/UpdateOrderItemStatusRequest.php
```

`OrderItemStatusController` should stay thin. It should accept the request, call `OrderItemStatusTransitionService`, and return a stable response.

`UpdateOrderItemStatusRequest` should validate input shape and enum values. It should not own transition rules.

### Services

```text
app/Services/OrderItemStatusTransitionService.php
app/Services/OrderItemStatusTransitionValidator.php
app/Services/OrderItemWebhookPayloadBuilder.php
app/Services/OrderItemWebhookDispatchService.php
app/Services/OrderFinalizationService.php
app/Services/OrderFinalizedWebhookPayloadBuilder.php
app/Services/OrderFinalizedWebhookDispatchService.php
```

`OrderItemStatusTransitionService` owns the controlled status-change path:

```text
Start transaction.
Lock and reload the order item.
Validate transition.
Update item status.
Create item status event.
Create sync event.
Dispatch domain event after commit.
```

`OrderItemStatusTransitionValidator` owns the allowed transition graph.

`OrderItemWebhookPayloadBuilder` builds webhook payloads from persisted records only.

`OrderItemWebhookDispatchService` wraps `Spatie\WebhookServer\WebhookCall` and supplies the URL, payload, secret, timestamp option, metadata, and tags.

`OrderFinalizationService` checks whether all items in the order are ready, selects the finalized `OrderStatus`, updates the order, creates the order-level status/sync records, and returns whether an order finalization occurred. It must run inside the status transition transaction after the parent order has been locked.

`OrderFinalizedWebhookPayloadBuilder` builds the finalized order webhook payload from persisted records only.

`OrderFinalizedWebhookDispatchService` wraps `Spatie\WebhookServer\WebhookCall` for order-level finalized status webhooks.

### Repositories

```text
app/Repositories/OrderItemRepository.php
app/Repositories/OrderRepository.php
app/Repositories/ItemStatusEventRepository.php
app/Repositories/OrderItemSyncEventRepository.php
app/Repositories/OrderStatusEventRepository.php
app/Repositories/OrderSyncEventRepository.php
```

`OrderItemRepository` should provide a row-locking lookup for status transitions.

`OrderRepository` should provide a row-locking lookup for the parent order when finalization is being evaluated.

`ItemStatusEventRepository` should append status history records.

`OrderItemSyncEventRepository` should create and update delivery ledger records:

```text
pending
processing
delivered
failed
```

It should update:

```text
attempts
last_attempted_at
delivered_at
response_status
last_error
```

`OrderStatusEventRepository` should append order-level finalized status records if the phase chooses an order-level status event table.

`OrderSyncEventRepository` should create and update the delivery ledger for order-level finalized status webhooks if the phase chooses domain-specific sync tables.

The order-level repositories should rely on database uniqueness for duplicate protection. Application checks are useful for friendly control flow, but the database constraint should be the final guardrail.

### Events

```text
app/Events/OrderItemStatusChanged.php
app/Events/OrderStatusFinalized.php
```

Events should carry persisted identifiers only.

`OrderItemStatusChanged` payload:

```text
item_status_event_id
order_item_sync_event_id
```

`OrderStatusFinalized` payload:

```text
order_status_event_id
order_sync_event_id
```

Do not carry transient request data as the source of truth.

### Listeners

```text
app/Listeners/QueueOrderItemStatusWebhook.php
app/Listeners/QueueOrderFinalizedWebhook.php
app/Listeners/MarkOrderItemWebhookDelivered.php
app/Listeners/RecordOrderItemWebhookFailure.php
app/Listeners/MarkOrderItemWebhookFinalFailure.php
app/Listeners/MarkOrderWebhookDelivered.php
app/Listeners/RecordOrderWebhookFailure.php
app/Listeners/MarkOrderWebhookFinalFailure.php
```

`QueueOrderItemStatusWebhook` handles `OrderItemStatusChanged`, loads persisted state, builds the payload, and dispatches the Spatie webhook job.

`QueueOrderFinalizedWebhook` handles `OrderStatusFinalized`, loads persisted state, builds the payload, and dispatches the Spatie webhook job.

The delivery-state listeners should listen to Spatie webhook events and use Spatie metadata to find the related sync event record.

Use item webhook metadata like:

```text
order_item_sync_event_id
item_status_event_id
order_item_id
to_status
```

Use order finalized webhook metadata like:

```text
order_sync_event_id
order_status_event_id
order_id
final_status
```

### Jobs

```text
app/Jobs/SendOrderItemStatusWebhook.php
app/Jobs/SendOrderFinalizedWebhook.php
```

Use `spatie/laravel-webhook-server` for HTTP delivery instead of hand-rolling webhook sending.

Configure Spatie's default `webhook_job` only if one webhook job class is enough. If item and order finalized webhooks need different unique keys or attempt hooks, call `useJob(...)` from the dispatch service so item webhooks use `SendOrderItemStatusWebhook` and order finalized webhooks use `SendOrderFinalizedWebhook`.

`SendOrderItemStatusWebhook` should:

```text
Extend Spatie's CallWebhookJob.
Implement Laravel unique job behaviour.
Use a uniqueness key for the same order item/status transition.
Increment order_item_sync_events.attempts immediately before each send attempt.
Set last_attempted_at immediately before each send attempt.
Let Spatie handle HTTP sending, signing, retry release, backoff, and webhook events.
```

Use a unique key like:

```text
order-item-status-webhook:{order_item_id}:{to_status}
```

`SendOrderFinalizedWebhook` should follow the same package-extension pattern and use a unique key like:

```text
order-finalized-webhook:{order_id}:{final_status}
```

If the phase plan decides stricter uniqueness is safer, use `order_item_sync_event_id` instead.

Laravel unique jobs prevent duplicate active jobs. They do not replace sync event records, which remain the business-level delivery ledger.

## Spatie Webhook Server

Use `spatie/laravel-webhook-server` for:

```text
Queued webhook delivery.
HMAC signing.
Timestamp headers.
Retry attempts.
Backoff strategy.
Success/failure/final-failure events.
Configurable webhook job class.
Webhook metadata.
Webhook tags.
```

Configure:

```text
config/webhook-server.php
  queue
  connection
  webhook_job
  tries
  timeout_in_seconds
  backoff_strategy
  verify_ssl
  signature_header_name
  timestamp_header_name
  tags
```

Store website integration config without exposing secrets:

```text
config/services.php
  website webhook URL
  website webhook signing secret
```

Use `WebhookCall::create()` through the dispatch service:

```text
url(...)
payload(...)
useSecret(...)
useTimestamp()
meta(...)
withTags(...)
dispatch()->afterCommit()
```

If webhook dispatch is already inside an after-commit listener path, avoid adding redundant transaction hooks. The key rule is that the Spatie job must not be queued before the status event and sync event are committed.

## Sync Event State

`order_item_sync_events` is the application-level delivery ledger.

Order-level finalized status webhooks need the same durable delivery tracking. Use either a dedicated `order_sync_events` table or a deliberately generalized sync event table, depending on the schema direction chosen in the phase plan.

Redis stores pending queued work.

Laravel `failed_jobs` stores exhausted operational queue failures.

`order_item_sync_events` stores business-level delivery state.

On send attempt start:

```text
status = processing
attempts incremented
last_attempted_at = current timestamp
```

On successful delivery:

```text
status = delivered
delivered_at = current timestamp
response_status = HTTP response status
last_error = null
```

On failed attempt:

```text
response_status = HTTP response status if available
last_error = error message
```

On final failure:

```text
status = failed
```

Increment attempts during the send attempt, not again in failure listeners.

## Webhook Payload

Build item webhook payloads from persisted records:

```text
orders
order_items
item_status_events
order_item_sync_events
```

Use this event type:

```text
order_item.status_updated
```

Payload fields:

```text
event_id
event_type
order_reference
order_item_id
item_name
from_status
to_status
created_at
```

Do not include unnecessary customer, payment, credential, or environment data.

Build order finalized webhook payloads from persisted records:

```text
orders
order_status_events
order_sync_events
```

Use this event type:

```text
order.status_finalized
```

Payload fields:

```text
event_id
event_type
order_id
order_reference
fulfillment_type
from_status
to_status
created_at
```

Do not include item-level details in the order finalized webhook unless the phase plan establishes that the website needs them.

## Security Expectations

Treat webhook URLs, signing secrets, third-party payloads, and background job payloads as sensitive or untrusted where appropriate.

Rules:

```text
Do not print or log webhook secrets.
Use HTTPS webhook URLs outside local development.
Keep Spatie signing enabled.
Use timestamp headers.
Include event_id for receiver deduplication.
Keep payloads minimal.
Use timing-safe signature comparison on the receiver side.
```

The receiving website should:

```text
Verify the signature.
Reject stale timestamps.
Deduplicate by event_id.
Ignore status changes that do not make sense for its current state.
```

Receiver implementation is out of scope for this backend phase.

## Verification Expectations

Add PHPUnit coverage for:

```text
Allowed transitions.
Rejected skipped transitions.
Rejected backwards transitions.
Rejected terminal transitions.
Transaction rollback not queueing webhook delivery.
Order item row locking where practical.
Status event creation.
Sync event creation.
OrderItemStatusChanged after-commit dispatch.
Spatie webhook job dispatch.
Unique webhook job key.
Attempts incrementing on send attempt start.
Delivered sync event update.
Failed attempt sync event update.
Final failure sync event update.
Missing webhook config failing safely.
Order status does not finalize until every item is ready.
Pickup orders finalize to ready_for_pickup.
Delivery orders finalize to ready_for_delivery.
Order finalization creates exactly one order-level sync event.
Order finalization queues the finalized order status webhook.
Order finalization is not duplicated when the last-item-ready path is retried.
Concurrent final item updates cannot create duplicate finalized order events.
Database uniqueness prevents duplicate finalized order sync records.
```

Run:

```text
composer test
composer analyse
```

Use repo test naming from `.docs/WRITING-TESTS.md`:

```text
camelCase method names
test prefix
docblocks when names would get too long
```

## Risks To Watch

```text
Dispatching before commit can queue a job that cannot see the records it needs.
Direct model updates can bypass transition rules and skip sync events.
Attempt counts can drift if incremented in multiple places.
Unique job locks need a shared cache store in multi-worker or multi-host deployments.
Missing order-level uniqueness constraints can allow duplicate finalized webhooks during concurrent updates.
Spatie final failure events must update order_item_sync_events or business reporting will drift.
Order finalized webhooks need their own delivery ledger; reusing item sync events without a schema decision will blur the domain model.
Receiver-side replay and stale-event protection are not solved by the sender alone.
```

## Final Flow

```text
POS requests item status update
  |
Controller delegates to service
  |
Service starts transaction
  |
Repository locks order item
  |
Validator checks requested transition
  |
Service updates order_items.status
  |
Service creates item_status_events
  |
Service creates order_item_sync_events
  |
If every item is ready, service finalizes order status as ready_for_pickup or ready_for_delivery
  |
Repository locks parent order and service rechecks sibling items
  |
Service creates order-level finalized status/sync records
  |
Transaction commits
  |
OrderItemStatusChanged dispatches after commit
  |
OrderStatusFinalized dispatches after commit when order was finalized
  |
Listener builds persisted webhook payload
  |
Listener dispatches Spatie webhook call for item status
  |
Listener dispatches Spatie webhook call for finalized order status when needed
  |
Spatie queues SendOrderItemStatusWebhook on Redis
  |
Spatie queues SendOrderFinalizedWebhook on Redis when needed
  |
Unique job behaviour prevents duplicate active job
  |
Job increments attempts and sends signed webhook
  |
Spatie retries failed delivery according to config
  |
Spatie success/failure events update the related item or order sync event
  |
Final failure marks the related sync event as failed
```
