<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_item_id', 'from_status', 'to_status'])]
class ItemStatusEvent extends Model
{
    protected function casts(): array
    {
        return [
            'from_status' => OrderItemStatus::class,
            'to_status' => OrderItemStatus::class,
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function syncEvents(): HasMany
    {
        return $this->hasMany(OrderItemSyncEvent::class);
    }
}
