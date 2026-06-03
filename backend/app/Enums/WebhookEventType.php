<?php

namespace App\Enums;

enum WebhookEventType: string
{
    case OrderItemStatusUpdated = 'order_item.status_updated';
    case OrderStatusChanged = 'order.status_changed';
}
