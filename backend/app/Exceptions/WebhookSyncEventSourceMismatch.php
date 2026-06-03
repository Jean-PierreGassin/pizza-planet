<?php

namespace App\Exceptions;

use App\Enums\WebhookEventType;
use RuntimeException;

class WebhookSyncEventSourceMismatch extends RuntimeException
{
    public static function forOrderItemStatus(WebhookEventType $eventType): self
    {
        return new self(
            message: "Webhook sync event for $eventType->value is not linked to an order item status event.",
        );
    }

    public static function forOrderStatus(WebhookEventType $eventType): self
    {
        return new self(
            message: "Webhook sync event for $eventType->value is not linked to an order status event.",
        );
    }
}
