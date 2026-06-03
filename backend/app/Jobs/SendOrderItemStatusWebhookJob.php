<?php

namespace App\Jobs;

class SendOrderItemStatusWebhookJob extends SendOrderStatusWebhookJob
{
    protected function uniquePrefix(): string
    {
        return 'order-item-status-webhook';
    }
}
