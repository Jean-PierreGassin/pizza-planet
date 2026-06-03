<?php

namespace App\Services;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Enums\WebhookEventType;
use App\Models\ItemStatusEvent;

class OrderItemWebhookPayloadBuilderService
{
    public function build(OrderItemStatusTransitionDTO $transition, ItemStatusEvent $itemStatusEvent): array
    {
        return [
            'event_id' => $itemStatusEvent->id,
            'event_type' => WebhookEventType::OrderItemStatusUpdated->value,
            'order_reference' => $transition->order->reference,
            'order_item_id' => $transition->orderItem->id,
            'item_name' => $transition->orderItem->name,
            'from_status' => $itemStatusEvent->from_status->value,
            'to_status' => $itemStatusEvent->to_status->value,
        ];
    }
}
