<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SyncEventStatus;
use App\Models\ItemStatusEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemSyncEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function testOrderModelsCastStatusesToEnums(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItem::factory()->create([
            'status' => OrderItemStatus::Baking,
        ]);
        $statusEvent = ItemStatusEvent::factory()->create([
            'from_status' => OrderItemStatus::Preparing,
            'to_status' => OrderItemStatus::Baking,
        ]);
        $syncEvent = OrderItemSyncEvent::factory()->create([
            'status' => SyncEventStatus::Processing,
        ]);

        $this->assertSame(OrderStatus::InProgress, $order->status);
        $this->assertSame(OrderItemStatus::Baking, $item->status);
        $this->assertSame(OrderItemStatus::Preparing, $statusEvent->from_status);
        $this->assertSame(OrderItemStatus::Baking, $statusEvent->to_status);
        $this->assertSame(SyncEventStatus::Processing, $syncEvent->status);
    }

    public function testOrderItemSyncEventsCastDeliveryState(): void
    {
        $lastAttemptedAt = Carbon::now()->subMinute();
        $deliveredAt = Carbon::now();

        $syncEvent = OrderItemSyncEvent::factory()->create([
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
        ], $syncEvent->payload);
        $this->assertInstanceOf(Carbon::class, $syncEvent->last_attempted_at);
        $this->assertInstanceOf(Carbon::class, $syncEvent->delivered_at);
        $this->assertSame($lastAttemptedAt->toDateTimeString(), $syncEvent->last_attempted_at->toDateTimeString());
        $this->assertSame($deliveredAt->toDateTimeString(), $syncEvent->delivered_at->toDateTimeString());
    }

    public function testOrderModelsExposeArchitectureRelationships(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->for($order)->create();
        $statusEvent = ItemStatusEvent::factory()->for($item, 'item')->create();
        $syncEvent = OrderItemSyncEvent::factory()->for($statusEvent, 'itemStatusEvent')->create();

        $this->assertTrue($order->items->first()->is($item));
        $this->assertTrue($item->order->is($order));
        $this->assertTrue($item->statusEvents->first()->is($statusEvent));
        $this->assertTrue($statusEvent->item->is($item));
        $this->assertTrue($statusEvent->syncEvents->first()->is($syncEvent));
        $this->assertTrue($syncEvent->itemStatusEvent->is($statusEvent));
    }
}
