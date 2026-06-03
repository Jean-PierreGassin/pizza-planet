<?php

namespace App\Models;

use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use Carbon\Carbon;
use Database\Factories\WebhookSyncEventModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $order_item_status_event_id
 * @property int|null $order_status_event_id
 * @property WebhookEventType $event_type
 * @property array<string, mixed> $payload
 * @property SyncEventStatus $status
 * @property Carbon|null $last_attempted_at
 * @property Carbon|null $delivered_at
 * @property OrderItemStatusEventModel|null $orderItemStatusEventModel
 * @property OrderStatusEventModel|null $orderStatusEventModel
 */
#[Fillable([
    'order_item_status_event_id',
    'order_status_event_id',
    'event_type',
    'destination_url',
    'payload',
    'status',
    'attempts',
    'last_attempted_at',
    'delivered_at',
    'last_error',
    'response_status',
])]
class WebhookSyncEventModel extends Model
{
    /** @use HasFactory<WebhookSyncEventModelFactory> */
    use HasFactory;

    protected $table = 'webhook_sync_events';

    protected function casts(): array
    {
        return [
            'event_type' => WebhookEventType::class,
            'payload' => 'array',
            'status' => SyncEventStatus::class,
            'last_attempted_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function orderItemStatusEventModel(): BelongsTo
    {
        return $this->belongsTo(
            related: OrderItemStatusEventModel::class,
            foreignKey: 'order_item_status_event_id',
        );
    }

    public function orderStatusEventModel(): BelongsTo
    {
        return $this->belongsTo(
            related: OrderStatusEventModel::class,
            foreignKey: 'order_status_event_id',
        );
    }
}
