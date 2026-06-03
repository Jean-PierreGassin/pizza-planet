<?php

namespace App\DTOs;

use App\Enums\OrderItemStatus;
use App\Models\ItemStatusEvent;
use App\Models\OrderItem;
use App\Models\OrderItemSyncEvent;

class OrderItemStatusTransitionResultDTO
{
    public function __construct(
        public readonly OrderItem $orderItem,
        public readonly OrderItemStatus $status,
        public readonly ItemStatusEvent $itemStatusEvent,
        public readonly OrderItemSyncEvent $syncEvent,
    ) {
    }
}
