<?php

namespace Tests\Feature;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Events\OrderItemStatusChangedEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
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
        Event::fake([OrderItemStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = Order::factory()->create([
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Baking,
        ]);
        OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Baking,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => OrderItemStatus::Ready->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::InProgress->value,
        ]);
    }

    #[DataProvider('finalizedOrderStatuses')]
    public function testApiFinalizesOrderWhenEveryItemIsReady(
        OrderFulfillmentType $fulfillmentType,
        OrderStatus $finalStatus,
    ): void {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = Order::factory()->create([
            'fulfillment_type' => $fulfillmentType,
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Baking,
        ]);
        OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Ready,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => OrderItemStatus::Ready->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => $finalStatus->value,
        ]);
    }

    public function testApiDoesNotFinalizeAlreadyFinalizedOrders(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = Order::factory()->create([
            'fulfillment_type' => OrderFulfillmentType::Delivery,
            'status' => OrderStatus::ReadyForPickup,
        ]);
        $item = OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Baking,
        ]);
        OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Ready,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => OrderItemStatus::Ready->value,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::ReadyForPickup->value,
        ]);
    }

    private function authenticate(): void
    {
        $this->actingAs(User::factory()->create());
    }

    public static function finalizedOrderStatuses(): array
    {
        return [
            'pickup' => [OrderFulfillmentType::Pickup, OrderStatus::ReadyForPickup],
            'delivery' => [OrderFulfillmentType::Delivery, OrderStatus::ReadyForDelivery],
        ];
    }
}
