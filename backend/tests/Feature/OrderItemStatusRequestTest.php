<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Events\OrderItemStatusChangedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\InteractsWithWebsiteWebhookConfig;
use Tests\TestCase;

class OrderItemStatusRequestTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithWebsiteWebhookConfig;

    public function testApiRequiresAuthentication(): void
    {
        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => 1,
            'order_item_id' => 1,
            'status' => OrderItemStatus::Preparing->value,
        ]);

        $response->assertUnauthorized();
    }

    public function testApiRejectsMissingOrderItemsBeforeTransition(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);

        $order = OrderModel::factory()->create();

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => 999999,
            'status' => OrderItemStatus::Preparing->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('order_item_id');

        $this->assertDatabaseCount('order_item_status_events', 0);
        $this->assertDatabaseCount('webhook_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    public function testApiRejectsOrderItemsThatDoNotBelongToTheOrder(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = OrderModel::factory()->create();
        $otherOrder = OrderModel::factory()->create();
        $item = OrderItemModel::factory()->for($otherOrder, 'order')->create([
            'status' => OrderItemStatus::Pending,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => OrderItemStatus::Preparing->value,
        ]);

        $response->assertNotFound();

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'status' => OrderItemStatus::Pending->value,
        ]);
        $this->assertDatabaseCount('order_item_status_events', 0);
        $this->assertDatabaseCount('webhook_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    private function authenticate(): void
    {
        $this->actingAs(UserModel::factory()->create());
    }
}
