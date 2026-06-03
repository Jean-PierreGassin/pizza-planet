<?php

namespace Tests\Unit;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

class OrderStatusTypesTest extends TestCase
{
    public function testOrderStatusExposesDatabaseValues(): void
    {
        $this->assertSame([
            'pending',
            'in_progress',
            'ready_for_pickup',
            'ready_for_delivery',
            'completed',
            'cancelled',
        ], array_map(
            fn (OrderStatus $status): string => $status->value,
            OrderStatus::cases(),
        ));
    }

    public function testOrderFulfillmentTypeExposesDatabaseValues(): void
    {
        $this->assertSame([
            'pickup',
            'delivery',
        ], array_map(
            fn (OrderFulfillmentType $fulfillmentType): string => $fulfillmentType->value,
            OrderFulfillmentType::cases(),
        ));
    }

    public function testOrderItemStatusExposesDatabaseValues(): void
    {
        $this->assertSame([
            'pending',
            'preparing',
            'baking',
            'ready',
        ], array_map(
            fn (OrderItemStatus $status): string => $status->value,
            OrderItemStatus::cases(),
        ));
    }

    public function testSyncEventStatusExposesDatabaseValues(): void
    {
        $this->assertSame([
            'pending',
            'processing',
            'delivered',
            'failed',
        ], array_map(
            fn (SyncEventStatus $status): string => $status->value,
            SyncEventStatus::cases(),
        ));
    }

    public function testWebhookEventTypeExposesPayloadValues(): void
    {
        $this->assertSame([
            'order_item.status_updated',
            'order.status_changed',
        ], array_map(
            fn (WebhookEventType $eventType): string => $eventType->value,
            WebhookEventType::cases(),
        ));
    }
}
