<?php

namespace App\Events;

use App\Models\OrderItemStatusEventModel;
use App\Models\WebhookSyncEventModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemStatusChangedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public OrderItemStatusEventModel $orderItemStatusEventModel,
        public WebhookSyncEventModel $syncEventModel,
    ) {
    }
}
