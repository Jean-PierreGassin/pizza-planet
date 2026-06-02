<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
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
