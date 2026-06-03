<?php

namespace Database\Factories;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderStatus;
use App\Models\OrderModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderModel>
 */
class OrderModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->bothify('PP-####'),
            'fulfillment_type' => OrderFulfillmentType::Pickup,
            'status' => OrderStatus::Pending,
        ];
    }
}
