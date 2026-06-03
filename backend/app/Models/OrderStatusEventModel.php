<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderStatusEventModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_id
 * @property OrderStatus $from_status
 * @property OrderStatus $to_status
 * @property OrderModel $order
 */
#[Fillable(['order_id', 'from_status', 'to_status'])]
class OrderStatusEventModel extends Model
{
    /** @use HasFactory<OrderStatusEventModelFactory> */
    use HasFactory;

    protected $table = 'order_status_events';

    protected function casts(): array
    {
        return [
            'from_status' => OrderStatus::class,
            'to_status' => OrderStatus::class,
        ];
    }

    /**
     * @return BelongsTo<OrderModel, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class);
    }

    public function syncEventModels(): HasMany
    {
        return $this->hasMany(
            related: WebhookSyncEventModel::class,
            foreignKey: 'order_status_event_id',
        );
    }
}
