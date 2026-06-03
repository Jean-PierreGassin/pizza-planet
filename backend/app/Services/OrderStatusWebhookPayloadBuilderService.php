<?php

namespace App\Services;

use App\Enums\WebhookEventType;
use App\Models\OrderStatusEventModel;

class OrderStatusWebhookPayloadBuilderService
{
    public function build(OrderStatusEventModel $orderStatusEventModel): array
    {
        $order = $orderStatusEventModel->order;

        return [
            'event_type' => WebhookEventType::OrderStatusChanged->value,
            'order_id' => $order->id,
            'order_reference' => $order->reference,
            'fulfillment_type' => $order->fulfillment_type->value,
            'from_status' => $orderStatusEventModel->from_status->value,
            'to_status' => $orderStatusEventModel->to_status->value,
            'created_at' => $orderStatusEventModel->created_at?->toJSON(),
        ];
    }
}
