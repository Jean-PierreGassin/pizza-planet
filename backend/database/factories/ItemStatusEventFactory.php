<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Models\ItemStatusEvent;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemStatusEvent>
 */
class ItemStatusEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'from_status' => OrderItemStatus::Pending,
            'to_status' => OrderItemStatus::Preparing,
        ];
    }
}
