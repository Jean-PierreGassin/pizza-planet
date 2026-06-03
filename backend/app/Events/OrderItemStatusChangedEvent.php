<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemStatusChangedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $itemStatusEventId,
        public int $orderItemSyncEventId,
    ) {
    }
}
