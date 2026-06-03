<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Events\OrderItemStatusChangedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\OrderItemStatusEventModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\WebhookSyncEventModel;
use App\Models\UserModel;
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
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = OrderModel::factory()->create([
            'reference' => 'PP-1001',
            'status' => OrderStatus::InProgress,
        ]);
        $item = OrderItemModel::factory()->for($order, 'order')->create([
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

        $statusEvent = OrderItemStatusEventModel::query()->firstOrFail();
        $syncEventModel = WebhookSyncEventModel::query()->firstOrFail();

        $this->assertSame($item->id, $statusEvent->order_item_id);
        $this->assertSame($fromStatus, $statusEvent->from_status);
        $this->assertSame($toStatus, $statusEvent->to_status);
        $this->assertSame($statusEvent->id, $syncEventModel->order_item_status_event_id);
        $this->assertSame(WebhookEventType::OrderItemStatusUpdated, $syncEventModel->event_type);
        $this->assertSame(SyncEventStatus::Pending, $syncEventModel->status);
        $this->assertSame($this->websiteWebhookUrl(), $syncEventModel->destination_url);

        $payload = $syncEventModel->getAttribute('payload');

        $this->assertIsArray($payload);
        $this->assertArrayNotHasKey('event_id', $payload);
        $this->assertSame(WebhookEventType::OrderItemStatusUpdated->value, $payload['event_type']);
        $this->assertSame('PP-1001', $payload['order_reference']);
        $this->assertSame('Pepperoni', $payload['item_name']);
        $this->assertSame($fromStatus->value, $payload['from_status']);
        $this->assertSame($toStatus->value, $payload['to_status']);
        $this->assertArrayNotHasKey('created_at', $payload);

        Event::assertDispatched(
            OrderItemStatusChangedEvent::class,
            fn (OrderItemStatusChangedEvent $event): bool => $event->orderItemStatusEventModel->is($statusEvent)
                && $event->syncEventModel->is($syncEventModel),
        );
    }

    #[DataProvider('rejectedTransitions')]
    public function testApiRejectsInvalidTransitionsWithoutPersistence(
        OrderItemStatus $fromStatus,
        OrderItemStatus $toStatus,
    ): void {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        $this->configureWebsiteWebhook();

        $order = OrderModel::factory()->create();
        $item = OrderItemModel::factory()->for($order, 'order')->create([
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
        $this->assertDatabaseCount('order_item_status_events', 0);
        $this->assertDatabaseCount('webhook_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    public function testApiRollsBackTransitionWhenWebsiteWebhookUrlIsMissing(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        config(['services.website.webhook_url' => null]);

        $order = OrderModel::factory()->create();
        $item = OrderItemModel::factory()->for($order, 'order')->create([
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
        $this->assertDatabaseCount('order_item_status_events', 0);
        $this->assertDatabaseCount('webhook_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    public function testApiRollsBackTransitionWhenWebsiteWebhookSecretIsMissing(): void
    {
        $this->authenticate();
        Event::fake([OrderItemStatusChangedEvent::class, OrderStatusChangedEvent::class]);
        config([
            'services.website.webhook_url' => $this->websiteWebhookUrl(),
            'services.website.webhook_secret' => null,
        ]);

        $order = OrderModel::factory()->create();
        $item = OrderItemModel::factory()->for($order, 'order')->create([
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
        $this->assertDatabaseCount('order_item_status_events', 0);
        $this->assertDatabaseCount('webhook_sync_events', 0);
        Event::assertNotDispatched(OrderItemStatusChangedEvent::class);
    }

    private function authenticate(): void
    {
        $this->actingAs(UserModel::factory()->create());
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
