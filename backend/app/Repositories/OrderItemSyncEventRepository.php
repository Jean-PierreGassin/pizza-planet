<?php

namespace App\Repositories;

use App\Enums\SyncEventStatus;
use App\Models\ItemStatusEvent;
use App\Models\OrderItemSyncEvent;

class OrderItemSyncEventRepository
{
    public function create(
        ItemStatusEvent $itemStatusEvent,
        string $destinationUrl,
        array $payload,
    ): OrderItemSyncEvent {
        return OrderItemSyncEvent::query()->create([
            'item_status_event_id' => $itemStatusEvent->id,
            'destination_url' => $destinationUrl,
            'payload' => $payload,
            'status' => SyncEventStatus::Pending,
            'attempts' => 0,
        ]);
    }
}
