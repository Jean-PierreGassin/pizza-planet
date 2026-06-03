<?php

namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Services\OrderStatusWebhookDispatchService;

class QueueOrderStatusWebhookListener
{
    public function __construct(
        private readonly OrderStatusWebhookDispatchService $webhooks,
    ) {
    }

    public function handle(OrderStatusChangedEvent $event): void
    {
        $this->webhooks->dispatchBySyncEventId(
            syncEventModelId: $event->syncEventModel->id,
        );
    }
}
