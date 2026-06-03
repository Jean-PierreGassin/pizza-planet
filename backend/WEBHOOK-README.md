# Website Webhooks

The backend uses webhooks to notify the website when order item status changes and when a parent order is finalized. This keeps the order workflow durable: the database records the status change first, then Redis queues delivery work, and the website receives a signed event it can verify and apply safely.

## Why Webhooks

Order status changes happen in the backend, but the website needs to reflect them without polling constantly. Webhooks let the backend push small event payloads to the website after the relevant database transaction commits.

The backend still records delivery state in MySQL. Redis only stores pending queued work, while sync event tables remain the business ledger for pending, processing, delivered, and failed webhook delivery.

## Why HMAC Signing

Webhook requests cross a trust boundary between the backend and the website. HMAC signing lets the website verify that a request was produced by the backend and that the payload was not changed in transit.

The signing secret must be shared only between the backend and the receiving website. Do not commit real webhook secrets, print them in logs, or paste them into issues, plans, or pull request text.

## Why Spatie Webhook Server

The backend uses `spatie/laravel-webhook-server` instead of hand-rolled HTTP delivery because it provides the pieces this workflow needs:

- queued webhook delivery
- HMAC request signing
- timestamp headers
- retries with backoff
- success, failure, and final-failure events
- metadata and tags for connecting package events back to sync records

## Local Configuration

The tracked `.env.example` contains safe placeholders:

```env
WEBSITE_WEBHOOK_URL=https://website.example.test/webhooks/pizza-planet
WEBSITE_WEBHOOK_SECRET=local-placeholder-signing-secret
WEBHOOK_QUEUE_CONNECTION=redis
WEBHOOK_QUEUE=webhooks
WEBHOOK_SIGNATURE_HEADER=X-Pizza-Planet-Signature
WEBHOOK_TIMESTAMP_HEADER=X-Pizza-Planet-Timestamp
WEBHOOK_TIMEOUT_SECONDS=3
WEBHOOK_TRIES=3
WEBHOOK_VERIFY_SSL=true
```

Set real values only in an untracked `.env` or in the deployment environment.

## Generate A Signing Secret

Generate a high-entropy secret locally:

```sh
composer webhook:secret
```

Copy the generated value into `WEBSITE_WEBHOOK_SECRET` in the backend environment and into the receiving website's matching secret setting.

For production, generate the secret in the deployment secret manager if one is available. Rotate the secret if it is exposed or copied into a place it does not belong.

## Receiver Expectations

The receiving website should:

- verify the HMAC signature
- reject stale timestamps
- deduplicate by event ID
- ignore status changes that do not make sense for its current state

Receiver implementation is outside this backend phase, but the sender includes event IDs, signs payloads, and sends timestamp headers so the receiver can enforce those checks.
