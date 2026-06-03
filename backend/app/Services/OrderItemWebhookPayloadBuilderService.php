<?php

namespace App\Services;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Enums\WebhookEventType;

class OrderItemWebhookPayloadBuilderService
{
    public function build(OrderItemStatusTransitionDTO $transition): array
    {
        return [
            'event_type' => WebhookEventType::OrderItemStatusUpdated->value,
            'order_reference' => $transition->order->reference,
            'order_item_id' => $transition->orderItem->id,
            'item_name' => $transition->orderItem->name,
            'from_status' => $transition->fromStatus->value,
            'to_status' => $transition->toStatus->value,
        ];
    }
}
