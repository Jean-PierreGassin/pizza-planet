<?php

namespace App\Repositories;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Models\OrderItemStatusEventModel;

class OrderItemStatusEventRepository
{
    public function create(OrderItemStatusTransitionDTO $transition): OrderItemStatusEventModel
    {
        return OrderItemStatusEventModel::query()->create([
            'order_item_id' => $transition->orderItem->id,
            'from_status' => $transition->fromStatus,
            'to_status' => $transition->toStatus,
        ]);
    }

    public function find(int $id): OrderItemStatusEventModel
    {
        return OrderItemStatusEventModel::query()->findOrFail($id);
    }
}
