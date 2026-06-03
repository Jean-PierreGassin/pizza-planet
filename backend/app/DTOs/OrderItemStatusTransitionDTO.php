<?php

namespace App\DTOs;

use App\Enums\OrderItemStatus;
use App\Models\OrderModel;
use App\Models\OrderItemModel;

class OrderItemStatusTransitionDTO
{
    public function __construct(
        public readonly OrderModel $order,
        public readonly OrderItemModel $orderItem,
        public readonly OrderItemStatus $fromStatus,
        public readonly OrderItemStatus $toStatus,
    ) {
    }

    public function toArray(): array
    {
        return [
            'order_item_id' => $this->orderItem->id,
            'status' => $this->toStatus->value,
        ];
    }
}
