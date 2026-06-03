<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Models\OrderItemStatusEventModel;
use App\Models\OrderItemModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemStatusEventModel>
 */
class OrderItemStatusEventModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItemModel::factory(),
            'from_status' => OrderItemStatus::Pending,
            'to_status' => OrderItemStatus::Preparing,
        ];
    }
}
