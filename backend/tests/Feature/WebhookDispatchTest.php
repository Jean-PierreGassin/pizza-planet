<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\WebhookEventType;
use App\Events\OrderItemStatusChangedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Exceptions\WebhookSyncEventSourceMismatch;
use App\Jobs\SendOrderItemStatusWebhookJob;
use App\Jobs\SendOrderStatusWebhookJob;
use App\Models\OrderItemStatusEventModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderStatusEventModel;
use App\Models\WebhookSyncEventModel;
use App\Services\OrderItemWebhookDispatchService;
use App\Services\OrderStatusWebhookDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Support\InteractsWithWebsiteWebhookConfig;
use Tests\TestCase;

class WebhookDispatchTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithWebsiteWebhookConfig;

    public function testItemWebhookDispatchQueuesSignedTimestampedJobWithSyncMetadata(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $syncEventModel = $this->createItemStatusSyncEvent();

        app(OrderItemWebhookDispatchService::class)->dispatchBySyncEventId(
            syncEventModelId: $syncEventModel->id,
        );

        Bus::assertDispatched(
            SendOrderItemStatusWebhookJob::class,
            fn (SendOrderItemStatusWebhookJob $job): bool => $job->webhookUrl === $this->websiteWebhookUrl()
                && $job->payload === $syncEventModel->payload
                && $job->useTimestamp === true
                && $job->afterCommit === true
                && $job->meta['webhook_sync_event_id'] === $syncEventModel->id
                && array_keys($job->meta) === ['webhook_sync_event_id']
                && $job->uniqueId() === 'order-item-status-webhook:'.$syncEventModel->id
                && array_key_exists(config('webhook-server.signature_header_name'), $job->headers),
        );
    }

    public function testOrderStatusWebhookDispatchQueuesSeparateJobWithSyncMetadata(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $syncEventModel = $this->createOrderStatusSyncEvent();

        app(OrderStatusWebhookDispatchService::class)->dispatchBySyncEventId(
            syncEventModelId: $syncEventModel->id,
        );

        Bus::assertDispatched(
            SendOrderStatusWebhookJob::class,
            fn (SendOrderStatusWebhookJob $job): bool => $job->webhookUrl === $this->websiteWebhookUrl()
                && $job->payload === $syncEventModel->payload
                && $job->useTimestamp === true
                && $job->afterCommit === true
                && $job->meta['webhook_sync_event_id'] === $syncEventModel->id
                && array_keys($job->meta) === ['webhook_sync_event_id']
                && $job->uniqueId() === 'order-status-webhook:'.$syncEventModel->id
                && array_key_exists(config('webhook-server.signature_header_name'), $job->headers),
        );
    }

    public function testDomainEventsQueueExpectedWebhookJobs(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $itemSyncEvent = $this->createItemStatusSyncEvent();
        $orderSyncEvent = $this->createOrderStatusSyncEvent();

        OrderItemStatusChangedEvent::dispatch(
            orderItemStatusEventModel: OrderItemStatusEventModel::query()->findOrFail($itemSyncEvent->order_item_status_event_id),
            syncEventModel: $itemSyncEvent,
        );
        OrderStatusChangedEvent::dispatch(
            orderStatusEventModel: OrderStatusEventModel::query()->findOrFail($orderSyncEvent->order_status_event_id),
            syncEventModel: $orderSyncEvent,
        );

        Bus::assertDispatched(SendOrderItemStatusWebhookJob::class);
        Bus::assertDispatched(SendOrderStatusWebhookJob::class);
    }

    public function testItemWebhookDispatchRejectsSyncEventsWithWrongEventType(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $syncEventModel = $this->createItemStatusSyncEvent([
            'event_type' => WebhookEventType::OrderStatusChanged,
        ]);

        $exception = null;

        try {
            app(OrderItemWebhookDispatchService::class)->dispatchBySyncEventId(
                syncEventModelId: $syncEventModel->id,
            );
        } catch (WebhookSyncEventSourceMismatch $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(WebhookSyncEventSourceMismatch::class, $exception);
        Bus::assertNothingDispatched();
    }

    public function testItemWebhookDispatchRejectsSyncEventsWithoutASource(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $syncEventModel = $this->createUnsourcedSyncEvent(
            eventType: WebhookEventType::OrderItemStatusUpdated,
        );

        $exception = null;

        try {
            app(OrderItemWebhookDispatchService::class)->dispatchBySyncEventId(
                syncEventModelId: $syncEventModel->id,
            );
        } catch (WebhookSyncEventSourceMismatch $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(WebhookSyncEventSourceMismatch::class, $exception);
        Bus::assertNothingDispatched();
    }

    public function testOrderStatusWebhookDispatchRejectsSyncEventsWithWrongEventType(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $syncEventModel = $this->createOrderStatusSyncEvent([
            'event_type' => WebhookEventType::OrderItemStatusUpdated,
        ]);

        $exception = null;

        try {
            app(OrderStatusWebhookDispatchService::class)->dispatchBySyncEventId(
                syncEventModelId: $syncEventModel->id,
            );
        } catch (WebhookSyncEventSourceMismatch $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(WebhookSyncEventSourceMismatch::class, $exception);
        Bus::assertNothingDispatched();
    }

    public function testOrderStatusWebhookDispatchRejectsSyncEventsWithoutASource(): void
    {
        $this->configureWebsiteWebhook();
        Bus::fake();

        $syncEventModel = $this->createUnsourcedSyncEvent(
            eventType: WebhookEventType::OrderStatusChanged,
        );

        $exception = null;

        try {
            app(OrderStatusWebhookDispatchService::class)->dispatchBySyncEventId(
                syncEventModelId: $syncEventModel->id,
            );
        } catch (WebhookSyncEventSourceMismatch $caught) {
            $exception = $caught;
        }

        $this->assertInstanceOf(WebhookSyncEventSourceMismatch::class, $exception);
        Bus::assertNothingDispatched();
    }

    private function createItemStatusSyncEvent(array $attributes = []): WebhookSyncEventModel
    {
        $order = OrderModel::factory()->create([
            'status' => OrderStatus::ReadyForPickup,
        ]);
        $item = OrderItemModel::factory()->for($order, 'order')->create([
            'status' => OrderItemStatus::Ready,
        ]);
        $orderItemStatusEventModel = OrderItemStatusEventModel::factory()->for($item, 'item')->create([
            'from_status' => OrderItemStatus::Baking,
            'to_status' => OrderItemStatus::Ready,
        ]);

        return WebhookSyncEventModel::factory()->for($orderItemStatusEventModel, 'orderItemStatusEventModel')->create(array_merge([
            'event_type' => WebhookEventType::OrderItemStatusUpdated,
            'destination_url' => $this->websiteWebhookUrl(),
            'payload' => [
                'event_type' => WebhookEventType::OrderItemStatusUpdated->value,
            ],
        ], $attributes));
    }

    private function createOrderStatusSyncEvent(array $attributes = []): WebhookSyncEventModel
    {
        $order = OrderModel::factory()->create([
            'status' => OrderStatus::ReadyForPickup,
        ]);
        $orderStatusEventModel = OrderStatusEventModel::factory()->for($order, 'order')->create([
            'from_status' => OrderStatus::InProgress,
            'to_status' => OrderStatus::ReadyForPickup,
        ]);

        return WebhookSyncEventModel::factory()->for($orderStatusEventModel, 'orderStatusEventModel')->create(array_merge([
            'order_item_status_event_id' => null,
            'event_type' => WebhookEventType::OrderStatusChanged,
            'destination_url' => $this->websiteWebhookUrl(),
            'payload' => [
                'event_type' => WebhookEventType::OrderStatusChanged->value,
            ],
        ], $attributes));
    }

    private function createUnsourcedSyncEvent(WebhookEventType $eventType): WebhookSyncEventModel
    {
        return WebhookSyncEventModel::factory()->create([
            'order_item_status_event_id' => null,
            'order_status_event_id' => null,
            'event_type' => $eventType,
            'destination_url' => $this->websiteWebhookUrl(),
            'payload' => [
                'event_type' => $eventType->value,
            ],
        ]);
    }
}
