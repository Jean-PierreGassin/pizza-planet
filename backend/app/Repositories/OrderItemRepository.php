<?php

namespace App\Repositories;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\DTOs\UpdateOrderItemStatusDTO;
use App\Enums\OrderItemStatus;
use App\Models\OrderItem;

class OrderItemRepository
{
    public function findForStatusTransition(UpdateOrderItemStatusDTO $data): OrderItemStatusTransitionDTO
    {
        $orderItem = OrderItem::query()
            ->with('order')
            ->whereKey($data->orderItemId)
            ->where('order_id', $data->orderId)
            ->lockForUpdate()
            ->firstOrFail();

        return new OrderItemStatusTransitionDTO(
            order: $orderItem->order,
            orderItem: $orderItem,
            fromStatus: $orderItem->status,
            toStatus: $data->status,
        );
    }

    public function updateStatus(OrderItem $orderItem, OrderItemStatus $status): OrderItem
    {
        $orderItem->setAttribute('status', $status);
        $orderItem->save();

        return $orderItem;
    }
}
