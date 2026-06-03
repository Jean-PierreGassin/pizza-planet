<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Events\OrderItemStatusChangedEvent;
use App\Models\ItemStatusEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemSyncEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\InteractsWithWebsiteWebhookConfig;
use Tests\TestCase;

class OrderItemStatusTransitionTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithWebsiteWebhookConfig;

    #[DataProvider('allowedTransitions')]
    public function testApiUpdatesOrderItemStatusThroughTransitionPath(
        OrderItemStatus $fromStatus,
        OrderItemStatus $toStatus,
    ): void {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = Order::factory()->create([
            'reference' => 'PP-1001',
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItem::factory()->for($order)->create([
            'name' => 'Pepperoni',
            'status' => $fromStatus,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => $toStatus->value,
        ]);

        $response->assertOk()
            ->assertJson([
                'order_item_id' => $item->id,
                'status' => $toStatus->value,
            ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'status' => $toStatus->value,
        ]);

        $statusEvent = ItemStatusEvent::query()->firstOrFail();
        $syncEvent = OrderItemSyncEvent::query()->firstOrFail();

        $this->assertSame($item->id, $statusEvent->order_item_id);
        $this->assertSame($fromStatus, $statusEvent->from_status);
        $this->assertSame($toStatus, $statusEvent->to_status);
        $this->assertSame($statusEvent->id, $syncEvent->item_status_event_id);
        $this->assertSame(SyncEventStatus::Pending, $syncEvent->status);
        $this->assertSame($this->websiteWebhookUrl(), $syncEvent->destination_url);

        $payload = $syncEvent->getAttribute('payload');

        $this->assertIsArray($payload);
        $this->assertSame($statusEvent->id, $payload['event_id']);
        $this->assertSame(WebhookEventType::OrderItemStatusUpdated->value, $payload['event_type']);
        $this->assertSame('PP-1001', $payload['order_reference']);
        $this->assertSame('Pepperoni', $payload['item_name']);
        $this->assertSame($fromStatus->value, $payload['from_status']);
        $this->assertSame($toStatus->value, $payload['to_status']);
        $this->assertArrayNotHasKey('created_at', $payload);

        Event::assertDispatched(
            OrderItemStatusChangedEvent::class,
            fn (OrderItemStatusChangedEvent $event): bool => $event->itemStatusEventId === $statusEvent->id
                && $event->orderItemSyncEventId === $syncEvent->id,
        );
    }

    #[DataProvider('rejectedTransitions')]
    public function testApiRejectsInvalidTransitionsWithoutPersistence(
        OrderItemStatus $fromStatus,
        OrderItemStatus $toStatus,
    ): void {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = Order::factory()->create();
        $item = OrderItem::factory()->for($order)->create([
            'status' => $fromStatus,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => $toStatus->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'status' => $fromStatus->value,
        ]);
        $this->assertDatabaseCount('item_status_events', 0);
        $this->assertDatabaseCount('order_item_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
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

    public function testApiRequiresAuthentication(): void
    {
        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => 1,
            'order_item_id' => 1,
            'status' => OrderItemStatus::Preparing->value,
        ]);

        $response->assertUnauthorized();
    }

    public function testApiRollsBackTransitionWhenWebsiteWebhookUrlIsMissing(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class]);
        config(['services.website.webhook_url' => null]);

        $order = Order::factory()->create();
        $item = OrderItem::factory()->for($order)->create([
            'status' => OrderItemStatus::Pending,
        ]);

        $response = $this->patchJson('/api/order-item-status', [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'status' => OrderItemStatus::Preparing->value,
        ]);

        $response->assertServerError();

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
