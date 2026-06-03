<?php

namespace Tests\Feature;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Events\OrderItemStatusChangedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderStatusEventModel;
use App\Models\WebhookSyncEventModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\InteractsWithWebsiteWebhookConfig;
use Tests\TestCase;

class OrderFinalizationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithWebsiteWebhookConfig;

    public function testApiDoesNotFinalizeOrderUntilEveryItemIsReady(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = OrderModel::factory()->create([
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Baking,
        ]);
        OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Baking,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/items/{$item->id}", [
            'status' => OrderItemStatus::Ready->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::InProgress->value,
        ]);
        $this->assertDatabaseCount('webhook_sync_events', 1);
        Event::assertNotDispatched(OrderStatusChangedEvent::class);
    }

    #[DataProvider('finalizedOrderStatuses')]
    public function testApiFinalizesOrderWhenEveryItemIsReady(
        OrderFulfillmentType $fulfillmentType,
        OrderStatus $finalStatus,
    ): void {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = OrderModel::factory()->create([
            'fulfillment_type' => $fulfillmentType,
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Baking,
        ]);
        OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Ready,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/items/{$item->id}", [
            'status' => OrderItemStatus::Ready->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => $finalStatus->value,
        ]);

        $syncEventModels = WebhookSyncEventModel::query()->orderBy('id')->get();
        $orderStatusEventModel = OrderStatusEventModel::query()->firstOrFail();

        $this->assertCount(2, $syncEventModels);
        $this->assertSame(WebhookEventType::OrderItemStatusUpdated, $syncEventModels[0]->event_type);
        $this->assertSame(WebhookEventType::OrderStatusChanged, $syncEventModels[1]->event_type);
        $this->assertNull($syncEventModels[1]->order_item_status_event_id);
        $this->assertSame($orderStatusEventModel->id, $syncEventModels[1]->order_status_event_id);
        $this->assertSame(SyncEventStatus::Pending, $syncEventModels[1]->status);
        $this->assertSame($this->websiteWebhookUrl(), $syncEventModels[1]->destination_url);
        $this->assertSame($order->id, $orderStatusEventModel->order_id);
        $this->assertSame(OrderStatus::InProgress, $orderStatusEventModel->from_status);
        $this->assertSame($finalStatus, $orderStatusEventModel->to_status);

        $payload = $syncEventModels[1]->getAttribute('payload');

        $this->assertIsArray($payload);
        $this->assertArrayNotHasKey('event_id', $payload);
        $this->assertSame(WebhookEventType::OrderStatusChanged->value, $payload['event_type']);
        $this->assertSame($order->id, $payload['order_id']);
        $this->assertSame($fulfillmentType->value, $payload['fulfillment_type']);
        $this->assertSame(OrderStatus::InProgress->value, $payload['from_status']);
        $this->assertSame($finalStatus->value, $payload['to_status']);
        $this->assertArrayHasKey('created_at', $payload);

        Event::assertDispatched(
            OrderStatusChangedEvent::class,
            fn (OrderStatusChangedEvent $event): bool => $event->orderStatusEventModel->is($orderStatusEventModel)
                && $event->syncEventModel->is($syncEventModels[1]),
        );
    }

    public function testApiDoesNotFinalizeAlreadyFinalizedOrders(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = OrderModel::factory()->create([
            'fulfillment_type' => OrderFulfillmentType::Delivery,
            'status' => OrderStatus::ReadyForPickup,
        ]);
        $item = OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Baking,
        ]);
        OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Ready,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$order->id}/items/{$item->id}", [
            'status' => OrderItemStatus::Ready->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::ReadyForPickup->value,
        ]);
        $this->assertDatabaseCount('webhook_sync_events', 1);
        Event::assertNotDispatched(OrderStatusChangedEvent::class);
    }

    private function authenticate(): void
    {
        $this->actingAs(UserModel::factory()->create());
    }

    public static function finalizedOrderStatuses(): array
    {
        return [
            'pickup' => [OrderFulfillmentType::Pickup, OrderStatus::ReadyForPickup],
            'delivery' => [OrderFulfillmentType::Delivery, OrderStatus::ReadyForDelivery],
        ];
    }
}
