<?php

namespace App\Models;

use App\Enums\SyncEventStatus;
use Carbon\Carbon;
use Database\Factories\OrderItemSyncEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_status_event_id
 * @property array<string, mixed> $payload
 * @property SyncEventStatus $status
 * @property Carbon|null $last_attempted_at
 * @property Carbon|null $delivered_at
 */
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
    /** @use HasFactory<OrderItemSyncEventFactory> */
    use HasFactory;

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
