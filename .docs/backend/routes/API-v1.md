# API v1 Routes

Pizza Planet is API-only for product behavior. The root web route remains available for local health checks, and order data is only available to authenticated API users.

## Demo User

Seeded local/demo user:

- Name: `Mario`
- Email: `mario@pizzaplanet.test`
- Password: `ilovepizza` typed manually when logging in

The frontend does not prefill credentials. The Postman environment includes the demo credentials so the collection can log in directly.

## Browser And Postman Session Flow

1. Request `GET /sanctum/csrf-cookie`.
2. Submit `POST /api/v1/sessions` with typed credentials.
3. Keep the returned cookies in the browser or Postman cookie jar.
4. Call protected `/api/v1` routes.

Local first-party requests should send:

- `Origin: http://127.0.0.1:5173`
- `Accept: application/json`
- `X-XSRF-TOKEN` on state-changing requests, using the value from the `XSRF-TOKEN` cookie. The Postman collection reads this from Postman's cookie jar before `POST`, `PATCH`, and `DELETE` requests.

The frontend dev server is pinned to `http://127.0.0.1:5173` so its origin matches `FRONTEND_URL`.

## Auth

### `POST /api/v1/sessions`

Request:

```json
{
  "email": "mario@pizzaplanet.test",
  "password": "typed manually"
}
```

Response:

```json
{
  "user": {
    "id": 1,
    "name": "Mario",
    "email": "mario@pizzaplanet.test"
  }
}
```

### `GET /api/v1/session`

Requires authentication.

### `DELETE /api/v1/session`

Requires authentication. Returns `204 No Content`.

## Orders

### `GET /api/v1/orders`

Requires authentication. Returns all orders for now.

### `GET /api/v1/orders/{order}`

Requires authentication. Returns one order with items.

### `PATCH /api/v1/orders/{order}/items/{item}`

Requires authentication. Moves one order item to the requested status when the transition is valid.

Request:

```json
{
  "status": "preparing"
}
```

Valid item status progression:

```txt
pending -> preparing -> baking -> ready
```

The `{item}` must belong to `{order}`. Cross-order item updates return `404`.

## Postman

Import these top-level files:

- `pizza-planet.postman_collection.json`
- `pizza-planet.postman_environment.json`

After import:

1. Select the `Pizza Planet Local` environment.
2. Confirm the imported environment has Mario's email and password values.
3. Run `Auth / Get CSRF Cookie`.
4. Run `Auth / Login`.
5. Run `Orders / List Orders`.
6. Use the saved `order_id` and `item_id` variables for detail and item status transition requests.
