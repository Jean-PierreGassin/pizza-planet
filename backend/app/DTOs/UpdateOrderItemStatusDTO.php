<?php

namespace App\DTOs;

use App\Enums\OrderItemStatus;

class UpdateOrderItemStatusDTO
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $orderItemId,
        public readonly OrderItemStatus $status,
    ) {
    }
}
