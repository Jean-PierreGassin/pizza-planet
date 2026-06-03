<?php

namespace App\Repositories;

use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Models\OrderItemStatusEventModel;
use App\Models\OrderStatusEventModel;
use App\Models\WebhookSyncEventModel;
use Carbon\Carbon;
use Spatie\WebhookServer\Events\WebhookCallEvent;

class WebhookSyncEventRepository
{
    public function createForOrderItemStatus(
        OrderItemStatusEventModel $orderItemStatusEventModel,
        string $destinationUrl,
        array $payload,
    ): WebhookSyncEventModel {
        return WebhookSyncEventModel::query()->create([
            'order_item_status_event_id' => $orderItemStatusEventModel->id,
            'order_status_event_id' => null,
            'event_type' => WebhookEventType::OrderItemStatusUpdated,
            'destination_url' => $destinationUrl,
            'payload' => $payload,
            'status' => SyncEventStatus::Pending,
            'attempts' => 0,
        ]);
    }

    public function createForOrderStatus(
        OrderStatusEventModel $orderStatusEventModel,
        string $destinationUrl,
        array $payload,
    ): WebhookSyncEventModel {
        return WebhookSyncEventModel::query()->create([
            'order_item_status_event_id' => null,
            'order_status_event_id' => $orderStatusEventModel->id,
            'event_type' => WebhookEventType::OrderStatusChanged,
            'destination_url' => $destinationUrl,
            'payload' => $payload,
            'status' => SyncEventStatus::Pending,
            'attempts' => 0,
        ]);
    }

    public function find(int $id): WebhookSyncEventModel
    {
        return WebhookSyncEventModel::query()->findOrFail($id);
    }

    public function findForOrderItemStatusDispatch(int $id): ?WebhookSyncEventModel
    {
        return WebhookSyncEventModel::query()
            ->with('orderItemStatusEventModel')
            ->whereKey($id)
            ->where('event_type', WebhookEventType::OrderItemStatusUpdated)
            ->first();
    }

    public function findForOrderStatusDispatch(int $id): ?WebhookSyncEventModel
    {
        return WebhookSyncEventModel::query()
            ->with('orderStatusEventModel')
            ->whereKey($id)
            ->where('event_type', WebhookEventType::OrderStatusChanged)
            ->first();
    }

    public function findFromWebhookMeta(array $meta): ?WebhookSyncEventModel
    {
        $syncEventModelId = $meta['webhook_sync_event_id'] ?? null;

        if (!is_int($syncEventModelId)) {
            return null;
        }

        return WebhookSyncEventModel::query()->find($syncEventModelId);
    }

    public function markProcessing(WebhookSyncEventModel $syncEventModel): WebhookSyncEventModel
    {
        $syncEventModel->setAttribute('status', SyncEventStatus::Processing);
        $syncEventModel->setAttribute('attempts', $syncEventModel->attempts + 1);
        $syncEventModel->setAttribute('last_attempted_at', Carbon::now());
        $syncEventModel->save();

        return $syncEventModel;
    }

    public function markDelivered(WebhookSyncEventModel $syncEventModel, WebhookCallEvent $event): WebhookSyncEventModel
    {
        $syncEventModel->setAttribute('status', SyncEventStatus::Delivered);
        $syncEventModel->setAttribute('delivered_at', Carbon::now());
        $syncEventModel->setAttribute('response_status', $event->response?->getStatusCode());
        $syncEventModel->setAttribute('last_error', null);
        $syncEventModel->save();

        return $syncEventModel;
    }

    public function recordFailure(WebhookSyncEventModel $syncEventModel, WebhookCallEvent $event): WebhookSyncEventModel
    {
        $syncEventModel->setAttribute('response_status', $event->response?->getStatusCode());
        $syncEventModel->setAttribute('last_error', $event->errorMessage);
        $syncEventModel->save();

        return $syncEventModel;
    }

    public function markFailed(WebhookSyncEventModel $syncEventModel, WebhookCallEvent $event): WebhookSyncEventModel
    {
        $this->recordFailure(
            syncEventModel: $syncEventModel,
            event: $event,
        );

        $syncEventModel->setAttribute('status', SyncEventStatus::Failed);
        $syncEventModel->save();

        return $syncEventModel;
    }
}
