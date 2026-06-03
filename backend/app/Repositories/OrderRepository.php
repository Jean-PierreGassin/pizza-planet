<?php

namespace App\Repositories;

use App\DTOs\OrderStatusTransitionDTO;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderModel;

class OrderRepository
{
    public function findForStatusTransition(int $orderId, OrderStatus $toStatus): OrderStatusTransitionDTO
    {
        $order = OrderModel::query()
            ->whereKey($orderId)
            ->lockForUpdate()
            ->firstOrFail();

        return new OrderStatusTransitionDTO(
            order: $order,
            fromStatus: $order->status,
            toStatus: $toStatus,
        );
    }

    public function hasItemsNotReady(OrderModel $order): bool
    {
        return $order->items()
            ->where('status', '!=', OrderItemStatus::Ready->value)
            ->exists();
    }

    public function updateStatus(OrderStatusTransitionDTO $transition): OrderStatusTransitionDTO
    {
        $transition->order->setAttribute('status', $transition->toStatus);
        $transition->order->save();

        return new OrderStatusTransitionDTO(
            order: $transition->order,
            fromStatus: $transition->fromStatus,
            toStatus: $transition->toStatus,
        );
    }

    public function find(int $id): OrderModel
    {
        return OrderModel::query()->findOrFail($id);
    }
}
