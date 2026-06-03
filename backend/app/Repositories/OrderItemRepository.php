<?php

namespace App\Repositories;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Enums\OrderItemStatus;
use App\Models\OrderItemModel;

class OrderItemRepository
{
    public function findForStatusTransition(
        int $orderId,
        int $orderItemId,
        OrderItemStatus $status,
    ): OrderItemStatusTransitionDTO
    {
        $orderItem = OrderItemModel::query()
            ->with('order')
            ->whereKey($orderItemId)
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->firstOrFail();

        return new OrderItemStatusTransitionDTO(
            order: $orderItem->order,
            orderItem: $orderItem,
            fromStatus: $orderItem->status,
            toStatus: $status,
        );
    }

    public function updateStatus(OrderItemStatusTransitionDTO $transition): OrderItemStatusTransitionDTO
    {
        $transition->orderItem->setAttribute('status', $transition->toStatus);
        $transition->orderItem->save();

        return new OrderItemStatusTransitionDTO(
            order: $transition->order,
            orderItem: $transition->orderItem,
            fromStatus: $transition->fromStatus,
            toStatus: $transition->toStatus,
        );
    }

    public function find(int $id): OrderItemModel
    {
        return OrderItemModel::query()->findOrFail($id);
    }
}
