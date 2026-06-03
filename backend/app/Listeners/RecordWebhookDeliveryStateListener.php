<?php

namespace App\Listeners;

use App\Services\WebhookDeliveryStateService;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

class RecordWebhookDeliveryStateListener
{
    public function __construct(
        private readonly WebhookDeliveryStateService $deliveryState,
    ) {
    }

    public function handleWebhookCallSucceededEvent(WebhookCallSucceededEvent $event): void
    {
        $this->deliveryState->record(event: $event);
    }

    public function handleWebhookCallFailedEvent(WebhookCallFailedEvent $event): void
    {
        $this->deliveryState->record(event: $event);
    }

    public function handleFinalWebhookCallFailedEvent(FinalWebhookCallFailedEvent $event): void
    {
        $this->deliveryState->record(event: $event);
    }
}
