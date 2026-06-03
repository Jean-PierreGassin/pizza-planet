# Order Architecture Setup

Set up the core order-tracking architecture for the Laravel application. The architecture should support orders, individual order items, item-level status tracking, status history, and sync-event persistence.

## 1. Database Architecture

### `orders`

Represents a single order.

#### Columns

```text
id
reference
fulfillment_type
status
created_at
updated_at
```

#### Purpose

```text
Stores the top-level order record.
Stores whether the order is for pickup or delivery.
Groups related order items together.
Provides an order-level status for filtering and display.
```

#### Relationships

```text
Order has many OrderItems
```

---

### `order_items`

Represents a single pizza within an order.

#### Columns

```text
id
order_id
name
status
created_at
updated_at
```

#### Purpose

```text
Stores each pizza as an individual item.
Tracks the current production status of each item.
Allows multiple pizzas to exist under one order.
```

#### Relationships

```text
OrderItem belongs to Order
OrderItem has many ItemStatusEvents
```

---

### `item_status_events`

Represents a historical record of item status changes.

#### Columns

```text
id
order_item_id
from_status
to_status
created_at
updated_at
```

#### Purpose

```text
Records each status transition for an order item.
Provides a status history separate from the current item status.
Supports auditability of item progression.
```

#### Relationships

```text
ItemStatusEvent belongs to OrderItem
ItemStatusEvent has many OrderItemSyncEvents
```

---

### `order_item_sync_events`

Represents a sync record linked to an item status event.

#### Columns

```text
id
item_status_event_id
destination_url
payload
status
attempts
last_attempted_at
delivered_at
last_error
response_status
created_at
updated_at
```

#### Purpose

```text
Stores the sync state for an item status event.
Keeps external delivery state separate from internal order state.
Provides the persistence layer for website update delivery.
```

#### Relationships

```text
OrderItemSyncEvent belongs to ItemStatusEvent
```

---

## 2. Relationship Summary

```text
orders
  └── order_items
        └── item_status_events
              └── order_item_sync_events
```

Expanded:

```text
Order
  has many OrderItems

OrderItem
  belongs to Order
  has many ItemStatusEvents

ItemStatusEvent
  belongs to OrderItem
  has many OrderItemSyncEvents

OrderItemSyncEvent
  belongs to ItemStatusEvent
```

---

## 3. PHP Enums

### `OrderStatus`

Used by:

```text
orders.status
```

Values:

```text
pending
in_progress
ready_for_pickup
ready_for_delivery
completed
cancelled
```

Purpose:

```text
Represents the aggregate status of an order.
```

---

### `OrderFulfillmentType`

Used by:

```text
orders.fulfillment_type
```

Values:

```text
pickup
delivery
```

Purpose:

```text
Represents how the customer will receive the order.
Lets the application derive the correct finalized order status.
```

---

### `OrderItemStatus`

Used by:

```text
order_items.status
item_status_events.from_status
item_status_events.to_status
```

Values:

```text
pending
preparing
baking
ready
```

Purpose:

```text
Represents the production status of an individual order item.
```

Status meaning:

```text
pending   → item has not started
preparing → preparation has started
baking    → item has been placed in the oven
ready     → item is ready for delivery or pickup
```

---

### `SyncEventStatus`

Used by:

```text
order_item_sync_events.status
```

Values:

```text
pending
processing
delivered
failed
```

Purpose:

```text
Represents the delivery state of a sync event.
```

---

## 4. Required Models

### `Order`

Represents:

```text
orders
```

Responsibilities:

```text
Expose order attributes.
Cast order status to OrderStatus.
Cast fulfillment type to OrderFulfillmentType.
Define the relationship to order items.
```

Relationships:

```text
items
```

---

### `OrderItem`

Represents:

```text
order_items
```

Responsibilities:

```text
Expose item attributes.
Cast item status to OrderItemStatus.
Define the relationship to the parent order.
Define the relationship to item status events.
```

Relationships:

```text
order
statusEvents
```

---

### `ItemStatusEvent`

Represents:

```text
item_status_events
```

Responsibilities:

```text
Expose item status event attributes.
Cast from_status and to_status to OrderItemStatus.
Define the relationship to the related order item.
Define the relationship to sync events.
```

Relationships:

```text
item
syncEvents
```

---

### `OrderItemSyncEvent`

Represents:

```text
order_item_sync_events
```

Responsibilities:

```text
Expose sync event attributes.
Cast sync event status to SyncEventStatus.
Cast payload as structured data.
Cast delivery timestamps as datetimes.
Define the relationship to the related item status event.
```

Relationships:

```text
itemStatusEvent
```

---

## 5. Implementation Order

### Phase 1: Domain Status Types

Goal:

```text
Create the shared enum types before any schema or model work depends on them.
```

Tasks:

```text
1. Create OrderStatus.
2. Create OrderItemStatus.
3. Create SyncEventStatus.
4. Confirm enum values match the database status columns they will cast.
```

---

### Phase 2: Database Foundation

Goal:

```text
Create the tables and persistence rules that define the order architecture.
```

Tasks:

```text
1. Create the orders migration.
2. Create the order_items migration.
3. Create the item_status_events migration.
4. Create the order_item_sync_events migration.
5. Add foreign keys between each parent and child table.
6. Add indexes for foreign keys and status lookups.
7. Add indexes for sync delivery lookups, including status and retry timing.
```

---

### Phase 3: Eloquent Model Layer

Goal:

```text
Create the model classes that expose the database architecture to application code.
```

Tasks:

```text
1. Create the Order model.
2. Create the OrderItem model.
3. Create the ItemStatusEvent model.
4. Create the OrderItemSyncEvent model.
5. Configure fillable or guarded attributes consistently across the models.
```

---

### Phase 4: Casts and Relationships

Goal:

```text
Wire the models to the enum and relationship contracts defined by the architecture.
```

Tasks:

```text
1. Cast orders.status to OrderStatus.
2. Cast order_items.status to OrderItemStatus.
3. Cast item_status_events.from_status and item_status_events.to_status to OrderItemStatus.
4. Cast order_item_sync_events.status to SyncEventStatus.
5. Cast order_item_sync_events.payload as structured data.
6. Cast sync delivery timestamps as datetimes.
7. Add Order.items.
8. Add OrderItem.order and OrderItem.statusEvents.
9. Add ItemStatusEvent.item and ItemStatusEvent.syncEvents.
10. Add OrderItemSyncEvent.itemStatusEvent.
```

---

### Phase 5: Test Support and Verification

Goal:

```text
Add the lightweight support needed to verify the architecture and use it in future work.
```

Tasks:

```text
1. Add basic model factories where they help local testing.
2. Add focused backend tests for model relationships and enum casts.
3. Run migrations in the test environment.
4. Run the relevant PHPUnit tests.
5. Run PHPStan if the implementation touches typed model or enum behaviour.
```

---

## 6. Final Architecture

```text
orders
  id
  reference
  status
  created_at
  updated_at

order_items
  id
  order_id
  name
  status
  created_at
  updated_at

item_status_events
  id
  order_item_id
  from_status
  to_status
  created_at
  updated_at

order_item_sync_events
  id
  item_status_event_id
  destination_url
  payload
  status
  attempts
  last_attempted_at
  delivered_at
  last_error
  response_status
  created_at
  updated_at
```
