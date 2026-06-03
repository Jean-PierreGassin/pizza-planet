<?php

namespace App\Jobs;

use App\Services\WebhookDeliveryStateService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Spatie\WebhookServer\CallWebhookJob;

class SendOrderStatusWebhookJob extends CallWebhookJob implements ShouldBeUnique
{
    public function uniqueId(): string
    {
        return $this->uniquePrefix().':'.$this->syncEventModelId();
    }

    public function handle(): mixed
    {
        app()->call(callback: [$this, 'markProcessing']);

        return parent::handle();
    }

    public function markProcessing(WebhookDeliveryStateService $deliveryState): void
    {
        $deliveryState->markProcessing(syncEventModelId: $this->syncEventModelId());
    }

    protected function uniquePrefix(): string
    {
        return 'order-status-webhook';
    }

    protected function syncEventModelId(): int
    {
        return $this->meta['webhook_sync_event_id'];
    }
}
