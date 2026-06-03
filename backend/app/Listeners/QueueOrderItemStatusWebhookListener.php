<?php

namespace App\Listeners;

use App\Events\OrderItemStatusChangedEvent;
use App\Services\OrderItemWebhookDispatchService;

class QueueOrderItemStatusWebhookListener
{
    public function __construct(
        private readonly OrderItemWebhookDispatchService $webhooks,
    ) {
    }

    public function handle(OrderItemStatusChangedEvent $event): void
    {
        $this->webhooks->dispatchBySyncEventId(
            syncEventModelId: $event->syncEventModel->id,
        );
    }
}
