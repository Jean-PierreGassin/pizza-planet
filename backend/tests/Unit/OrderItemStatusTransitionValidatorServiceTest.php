<?php

namespace Tests\Unit;

use App\Enums\OrderItemStatus;
use App\Exceptions\InvalidOrderItemStatusTransition;
use App\Services\OrderItemStatusTransitionValidatorService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrderItemStatusTransitionValidatorServiceTest extends TestCase
{
    #[DataProvider('allowedTransitions')]
    public function testAllowedTransitionsPass(OrderItemStatus $fromStatus, OrderItemStatus $toStatus): void
    {
        $validator = new OrderItemStatusTransitionValidatorService();

        $validator->validate($fromStatus, $toStatus);

        $this->addToAssertionCount(1);
    }

    #[DataProvider('rejectedTransitions')]
    public function testRejectedTransitionsFail(OrderItemStatus $fromStatus, OrderItemStatus $toStatus): void
    {
        $validator = new OrderItemStatusTransitionValidatorService();

        $this->expectException(InvalidOrderItemStatusTransition::class);

        $validator->validate($fromStatus, $toStatus);
    }

    public static function allowedTransitions(): array
    {
        return [
            'pending to preparing' => [OrderItemStatus::Pending, OrderItemStatus::Preparing],
            'preparing to baking' => [OrderItemStatus::Preparing, OrderItemStatus::Baking],
            'baking to ready' => [OrderItemStatus::Baking, OrderItemStatus::Ready],
        ];
    }

    public static function rejectedTransitions(): array
    {
        return [
            'pending to pending' => [OrderItemStatus::Pending, OrderItemStatus::Pending],
            'pending to baking' => [OrderItemStatus::Pending, OrderItemStatus::Baking],
            'pending to ready' => [OrderItemStatus::Pending, OrderItemStatus::Ready],
            'preparing to pending' => [OrderItemStatus::Preparing, OrderItemStatus::Pending],
            'preparing to preparing' => [OrderItemStatus::Preparing, OrderItemStatus::Preparing],
            'preparing to ready' => [OrderItemStatus::Preparing, OrderItemStatus::Ready],
            'baking to pending' => [OrderItemStatus::Baking, OrderItemStatus::Pending],
            'baking to preparing' => [OrderItemStatus::Baking, OrderItemStatus::Preparing],
            'baking to baking' => [OrderItemStatus::Baking, OrderItemStatus::Baking],
            'ready to pending' => [OrderItemStatus::Ready, OrderItemStatus::Pending],
            'ready to preparing' => [OrderItemStatus::Ready, OrderItemStatus::Preparing],
            'ready to baking' => [OrderItemStatus::Ready, OrderItemStatus::Baking],
            'ready to ready' => [OrderItemStatus::Ready, OrderItemStatus::Ready],
        ];
    }
}
