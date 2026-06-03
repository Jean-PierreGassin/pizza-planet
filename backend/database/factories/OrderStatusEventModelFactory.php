<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\OrderModel;
use App\Models\OrderStatusEventModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusEventModel>
 */
class OrderStatusEventModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => OrderModel::factory(),
            'from_status' => OrderStatus::InProgress,
            'to_status' => OrderStatus::ReadyForPickup,
        ];
    }
}
