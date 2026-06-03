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

    public function named(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderItemStatus::Pending,
        ]);
    }
}
