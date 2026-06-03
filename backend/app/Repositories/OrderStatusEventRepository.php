<?php

namespace App\Repositories;

use App\DTOs\OrderStatusTransitionDTO;
use App\Models\OrderStatusEventModel;

class OrderStatusEventRepository
{
    public function create(OrderStatusTransitionDTO $transition): OrderStatusEventModel
    {
        $orderStatusEventModel = OrderStatusEventModel::query()->create([
            'order_id' => $transition->order->id,
            'from_status' => $transition->fromStatus,
            'to_status' => $transition->toStatus,
        ]);

        $orderStatusEventModel->setRelation(
            relation: 'order',
            value: $transition->order,
        );

        return $orderStatusEventModel;
    }

    public function find(int $id): OrderStatusEventModel
    {
        return OrderStatusEventModel::query()->findOrFail($id);
    }
}
