<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Exceptions\InvalidOrderItemStatusTransition;

class OrderItemStatusTransitionValidatorService
{
    public function validate(OrderItemStatus $fromStatus, OrderItemStatus $toStatus): void
    {
        if ($this->nextStatus($fromStatus) !== $toStatus) {
            throw InvalidOrderItemStatusTransition::fromStatuses($fromStatus, $toStatus);
        }
    }

    private function nextStatus(OrderItemStatus $status): ?OrderItemStatus
    {
        return match ($status) {
            OrderItemStatus::Pending => OrderItemStatus::Preparing,
            OrderItemStatus::Preparing => OrderItemStatus::Baking,
            OrderItemStatus::Baking => OrderItemStatus::Ready,
            OrderItemStatus::Ready => null,
        };
    }
}
