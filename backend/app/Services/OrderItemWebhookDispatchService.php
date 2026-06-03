<?php

namespace App\Services;

use App\Jobs\SendOrderItemStatusWebhookJob;
use App\Enums\WebhookEventType;
use App\Exceptions\WebhookSyncEventSourceMismatch;
use App\Repositories\WebhookSyncEventRepository;

class OrderItemWebhookDispatchService
{
    public function __construct(
        private readonly WebhookSyncEventRepository $syncEventModels,
        private readonly WebsiteWebhookConfigurationService $webhookConfiguration,
    ) {
    }

    public function dispatchBySyncEventId(int $syncEventModelId): void
    {
        $syncEventModel = $this->syncEventModels->findForOrderItemStatusDispatch(id: $syncEventModelId);

        if ($syncEventModel === null || $syncEventModel->orderItemStatusEventModel === null) {
            throw WebhookSyncEventSourceMismatch::forOrderItemStatus(
                eventType: WebhookEventType::OrderItemStatusUpdated,
            );
        }

        $this->webhookConfiguration->webhookCall(jobClass: SendOrderItemStatusWebhookJob::class)
            ->url(url: $syncEventModel->destination_url)
            ->payload(payload: $syncEventModel->payload)
            ->meta(meta: [
                'webhook_sync_event_id' => $syncEventModel->id,
            ])
            ->withTags(tags: ['pizza-planet', 'order-item-status'])
            ->dispatch()
            ->afterCommit();
    }
}
