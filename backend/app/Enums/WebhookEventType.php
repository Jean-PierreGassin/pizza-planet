<?php

namespace App\Enums;

enum WebhookEventType: string
{
    case OrderItemStatusUpdated = 'order_item.status_updated';
    case OrderStatusFinalized = 'order.status_finalized';
}
