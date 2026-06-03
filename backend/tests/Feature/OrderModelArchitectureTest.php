<?php

namespace Tests\Feature;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Models\OrderItemStatusEventModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderStatusEventModel;
use App\Models\WebhookSyncEventModel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function testOrderModelsCastStatusesToEnums(): void
    {
        $order = OrderModel::factory()->create([
            'fulfillment_type' => OrderFulfillmentType::Delivery,
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItemModel::factory()->create([
            'status' => OrderItemStatus::Baking,
        ]);
        $statusEvent = OrderItemStatusEventModel::factory()->create([
            'from_status' => OrderItemStatus::Preparing,
            'to_status' => OrderItemStatus::Baking,
        ]);
        $orderStatusEventModel = OrderStatusEventModel::factory()->create([
            'from_status' => OrderStatus::InProgress,
            'to_status' => OrderStatus::ReadyForPickup,
        ]);
        $syncEventModel = WebhookSyncEventModel::factory()->create([
            'event_type' => WebhookEventType::OrderStatusChanged,
            'order_item_status_event_id' => null,
            'order_status_event_id' => $orderStatusEventModel->id,
            'status' => SyncEventStatus::Processing,
        ]);

        $this->assertSame(OrderFulfillmentType::Delivery, $order->fulfillment_type);
        $this->assertSame(OrderStatus::InProgress, $order->status);
        $this->assertSame(OrderItemStatus::Baking, $item->status);
        $this->assertSame(OrderItemStatus::Preparing, $statusEvent->from_status);
        $this->assertSame(OrderItemStatus::Baking, $statusEvent->to_status);
        $this->assertSame(OrderStatus::InProgress, $orderStatusEventModel->from_status);
        $this->assertSame(OrderStatus::ReadyForPickup, $orderStatusEventModel->to_status);
        $this->assertSame(WebhookEventType::OrderStatusChanged, $syncEventModel->event_type);
        $this->assertSame(SyncEventStatus::Processing, $syncEventModel->status);
    }

    public function testWebhookSyncEventModelsCastDeliveryState(): void
    {
        $lastAttemptedAt = Carbon::now()->subMinute();
        $deliveredAt = Carbon::now();

        $syncEventModel = WebhookSyncEventModel::factory()->create([
            'payload' => [
                'order_reference' => 'PP-1001',
                'status' => OrderItemStatus::Ready->value,
            ],
            'last_attempted_at' => $lastAttemptedAt,
            'delivered_at' => $deliveredAt,
        ]);

        $this->assertSame([
            'order_reference' => 'PP-1001',
            'status' => 'ready',
        ], $syncEventModel->payload);
        $this->assertInstanceOf(Carbon::class, $syncEventModel->last_attempted_at);
        $this->assertInstanceOf(Carbon::class, $syncEventModel->delivered_at);
        $this->assertSame($lastAttemptedAt->toDateTimeString(), $syncEventModel->last_attempted_at->toDateTimeString());
        $this->assertSame($deliveredAt->toDateTimeString(), $syncEventModel->delivered_at->toDateTimeString());
    }

    public function testOrderModelsExposeArchitectureRelationships(): void
    {
        $order = OrderModel::factory()->create();
        $item = OrderItemModel::factory()->for($order, 'order')->create();
        $statusEvent = OrderItemStatusEventModel::factory()->for($item, 'item')->create();
        $orderStatusEventModel = OrderStatusEventModel::factory()->for($order, 'order')->create();
        $itemSyncEvent = WebhookSyncEventModel::factory()->for($statusEvent, 'orderItemStatusEventModel')->create();
        $orderSyncEvent = WebhookSyncEventModel::factory()->for($orderStatusEventModel, 'orderStatusEventModel')->create([
            'order_item_status_event_id' => null,
            'event_type' => WebhookEventType::OrderStatusChanged,
        ]);

        $this->assertTrue($order->items->first()->is($item));
        $this->assertTrue($order->statusEvents->first()->is($orderStatusEventModel));
        $this->assertTrue($item->order->is($order));
        $this->assertTrue($item->statusEvents->first()->is($statusEvent));
        $this->assertTrue($statusEvent->item->is($item));
        $this->assertTrue($statusEvent->syncEventModels->first()->is($itemSyncEvent));
        $this->assertTrue($orderStatusEventModel->syncEventModels->first()->is($orderSyncEvent));
        $this->assertTrue($itemSyncEvent->orderItemStatusEventModel->is($statusEvent));
        $this->assertTrue($orderSyncEvent->orderStatusEventModel->is($orderStatusEventModel));
    }
}
