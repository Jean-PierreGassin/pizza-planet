<?php

namespace App\Events;

use App\Models\OrderStatusEventModel;
use App\Models\WebhookSyncEventModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public OrderStatusEventModel $orderStatusEventModel,
        public WebhookSyncEventModel $syncEventModel,
    ) {
    }
}
