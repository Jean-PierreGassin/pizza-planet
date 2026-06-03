<?php

namespace App\Services;

use App\Jobs\SendOrderStatusWebhookJob;
use App\Enums\WebhookEventType;
use App\Exceptions\WebhookSyncEventSourceMismatch;
use App\Repositories\WebhookSyncEventRepository;

class OrderStatusWebhookDispatchService
{
    public function __construct(
        private readonly WebhookSyncEventRepository $syncEventModels,
        private readonly WebsiteWebhookConfigurationService $webhookConfiguration,
    ) {
    }

    public function dispatchBySyncEventId(int $syncEventModelId): void
    {
        $syncEventModel = $this->syncEventModels->findForOrderStatusDispatch(id: $syncEventModelId);

        if ($syncEventModel === null || $syncEventModel->orderStatusEventModel === null) {
            throw WebhookSyncEventSourceMismatch::forOrderStatus(
                eventType: WebhookEventType::OrderStatusChanged,
            );
        }

        $this->webhookConfiguration->webhookCall(jobClass: SendOrderStatusWebhookJob::class)
            ->url(url: $syncEventModel->destination_url)
            ->payload(payload: $syncEventModel->payload)
            ->meta(meta: [
                'webhook_sync_event_id' => $syncEventModel->id,
            ])
            ->withTags(tags: ['pizza-planet', 'order-status-changed'])
            ->dispatch()
            ->afterCommit();
    }
}
