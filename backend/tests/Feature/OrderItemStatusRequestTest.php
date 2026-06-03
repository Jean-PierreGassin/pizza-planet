<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Events\OrderItemStatusChangedEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
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
        Event::fake([OrderItemStatusChangedEvent::class]);

        $order = Order::factory()->create();

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => 999999,
            'status' => OrderItemStatus::Preparing->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('order_item_id');

        $this->assertDatabaseCount('item_status_events', 0);
        $this->assertDatabaseCount('order_item_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    public function testApiRejectsOrderItemsThatDoNotBelongToTheOrder(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = Order::factory()->create();
        $otherOrder = Order::factory()->create();
        $item = OrderItem::factory()->for($otherOrder)->create([
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
        $this->assertDatabaseCount('item_status_events', 0);
        $this->assertDatabaseCount('order_item_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    private function authenticate(): void
    {
        $this->actingAs(User::factory()->create());
    }
}
