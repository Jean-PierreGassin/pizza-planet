<?php

namespace App\Services;

use App\DTOs\OrderItemStatusTransitionDTO;
use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\OrderRepository;

class OrderFinalizationService
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {
    }

    public function finalizeIfReady(OrderItemStatusTransitionDTO $transition): ?Order
    {
        if ($transition->toStatus !== OrderItemStatus::Ready) {
            return null;
        }

        $finalization = $this->orders->findForFinalization($transition);

        if ($this->isFinalized($finalization->order->status)) {
            return null;
        }

        if ($this->orders->hasItemsNotReady($finalization->order)) {
            return null;
        }

        return $this->orders->updateStatus(
            order: $finalization->order,
            status: $this->finalStatusFor($finalization->order),
        );
    }

    private function isFinalized(OrderStatus $status): bool
    {
        return in_array($status, [
            OrderStatus::ReadyForPickup,
            OrderStatus::ReadyForDelivery,
        ], true);
    }

    private function finalStatusFor(Order $order): OrderStatus
    {
        return match ($order->fulfillment_type) {
            OrderFulfillmentType::Pickup => OrderStatus::ReadyForPickup,
            OrderFulfillmentType::Delivery => OrderStatus::ReadyForDelivery,
        };
    }
}
