<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemModel>
 */
class OrderItemModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => OrderModel::factory(),
            'name' => fake()->randomElement([
                'Margherita',
                'Pepperoni',
                'Hawaiian',
                'Vegetarian',
            ]),
            'status' => OrderItemStatus::Pending,
        ];
    }
}
