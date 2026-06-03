<?php

namespace App\DTOs;

use App\Enums\OrderItemStatus;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemStatusTransitionDTO
{
    public function __construct(
        public readonly Order $order,
        public readonly OrderItem $orderItem,
        public readonly OrderItemStatus $fromStatus,
        public readonly OrderItemStatus $toStatus,
    ) {
    }
}
