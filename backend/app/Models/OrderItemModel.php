<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use Database\Factories\OrderItemModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_id
 * @property string $name
 * @property OrderItemStatus $status
 * @property OrderModel $order
 */
#[Fillable(['order_id', 'name', 'status'])]
class OrderItemModel extends Model
{
    /** @use HasFactory<OrderItemModelFactory> */
    use HasFactory;

    protected $table = 'order_items';

    protected function casts(): array
    {
        return [
            'status' => OrderItemStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(
            related: OrderItemStatusEventModel::class,
            foreignKey: 'order_item_id',
        );
    }
}
