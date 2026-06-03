<?php

namespace App\Services;

use App\Repositories\WebhookSyncEventRepository;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

class WebhookDeliveryStateService
{
    public function __construct(
        private readonly WebhookSyncEventRepository $syncEventModels,
    ) {
    }

    public function markProcessing(int $syncEventModelId): void
    {
        $this->syncEventModels->markProcessing(
            syncEventModel: $this->syncEventModels->find(id: $syncEventModelId),
        );
    }

    public function record(WebhookCallEvent $event): void
    {
        $syncEventModel = $this->syncEventModels->findFromWebhookMeta(meta: $event->meta);

        if ($syncEventModel === null) {
            return;
        }

        // TODO: Add a scheduled recovery command that requeues failed sync events after the normal try limit.
        match (true) {
            $event instanceof WebhookCallSucceededEvent => $this->syncEventModels->markDelivered(
                syncEventModel: $syncEventModel,
                event: $event,
            ),
            $event instanceof FinalWebhookCallFailedEvent => $this->syncEventModels->markFailed(
                syncEventModel: $syncEventModel,
                event: $event,
            ),
            $event instanceof WebhookCallFailedEvent => $this->syncEventModels->recordFailure(
                syncEventModel: $syncEventModel,
                event: $event,
            ),
            default => null,
        };
    }
}
