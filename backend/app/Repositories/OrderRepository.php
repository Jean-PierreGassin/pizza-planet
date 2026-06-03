<?php

namespace App\Repositories;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;

class OrderRepository
{
    public function findForFinalization(OrderItemStatusTransitionDTO $transition): OrderItemStatusTransitionDTO
    {
        $order = Order::query()
            ->whereKey($transition->order->id)
            ->lockForUpdate()
            ->firstOrFail();

        return new OrderItemStatusTransitionDTO(
            order: $order,
            orderItem: $transition->orderItem,
            fromStatus: $transition->fromStatus,
            toStatus: $transition->toStatus,
        );
    }

    public function hasItemsNotReady(Order $order): bool
    {
        return $order->items()
            ->where('status', '!=', OrderItemStatus::Ready->value)
            ->exists();
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->setAttribute('status', $status);
        $order->save();

        return $order;
    }
}
