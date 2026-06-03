<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use Database\Factories\OrderItemStatusEventModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_item_id
 * @property OrderItemStatus $from_status
 * @property OrderItemStatus $to_status
 */
#[Fillable(['order_item_id', 'from_status', 'to_status'])]
class OrderItemStatusEventModel extends Model
{
    /** @use HasFactory<OrderItemStatusEventModelFactory> */
    use HasFactory;

    protected $table = 'order_item_status_events';

    protected function casts(): array
    {
        return [
            'from_status' => OrderItemStatus::class,
            'to_status' => OrderItemStatus::class,
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItemModel::class, 'order_item_id');
    }

    public function syncEventModels(): HasMany
    {
        return $this->hasMany(
            related: WebhookSyncEventModel::class,
            foreignKey: 'order_item_status_event_id',
        );
    }
}
