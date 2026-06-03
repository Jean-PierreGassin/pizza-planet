<?php

namespace App\Models;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderStatus;
use Database\Factories\OrderModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $reference
 * @property OrderFulfillmentType $fulfillment_type
 * @property OrderStatus $status
 * @property \Illuminate\Database\Eloquent\Collection<int, OrderItemModel> $items
 */
#[Fillable(['reference', 'fulfillment_type', 'status'])]
class OrderModel extends Model
{
    /** @use HasFactory<OrderModelFactory> */
    use HasFactory;

    protected $table = 'orders';

    protected function casts(): array
    {
        return [
            'fulfillment_type' => OrderFulfillmentType::class,
            'status' => OrderStatus::class,
        ];
    }

    /**
     * @return HasMany<OrderItemModel, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(
            related: OrderItemModel::class,
            foreignKey: 'order_id',
        );
    }

    /**
     * @return HasMany<OrderStatusEventModel, $this>
     */
    public function statusEvents(): HasMany
    {
        return $this->hasMany(
            related: OrderStatusEventModel::class,
            foreignKey: 'order_id',
        );
    }
}
