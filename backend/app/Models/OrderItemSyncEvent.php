<?php

namespace App\Models;

use App\Enums\SyncEventStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'item_status_event_id',
    'destination_url',
    'payload',
    'status',
    'attempts',
    'last_attempted_at',
    'delivered_at',
    'last_error',
    'response_status',
])]
class OrderItemSyncEvent extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => SyncEventStatus::class,
            'last_attempted_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function itemStatusEvent(): BelongsTo
    {
        return $this->belongsTo(ItemStatusEvent::class);
    }
}
