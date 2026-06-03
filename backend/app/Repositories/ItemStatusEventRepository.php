<?php

namespace App\Repositories;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Models\ItemStatusEvent;

class ItemStatusEventRepository
{
    public function create(OrderItemStatusTransitionDTO $transition): ItemStatusEvent
    {
        return ItemStatusEvent::query()->create([
            'order_item_id' => $transition->orderItem->id,
            'from_status' => $transition->fromStatus,
            'to_status' => $transition->toStatus,
        ]);
    }
}
