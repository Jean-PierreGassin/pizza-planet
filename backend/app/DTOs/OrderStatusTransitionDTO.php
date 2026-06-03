<?php

namespace App\DTOs;

use App\Enums\OrderStatus;
use App\Models\OrderModel;

class OrderStatusTransitionDTO
{
    public function __construct(
        public readonly OrderModel $order,
        public readonly OrderStatus $fromStatus,
        public readonly OrderStatus $toStatus,
    ) {
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->toStatus->value,
        ];
    }
}
