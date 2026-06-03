<?php

namespace Database\Seeders;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\UserModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserModel::query()->updateOrCreate(
            attributes: ['email' => 'mario@pizzaplanet.test'],
            values: [
                'name' => 'Mario',
                'password' => 'ilovepizza',
            ],
        );

        $orders = [
            [
                'reference' => 'PP-MOON-001',
                'fulfillment_type' => OrderFulfillmentType::Delivery,
                'items' => [
                    'Galactic Garlic Knots',
                    'Extra-Terrestrial Pepperoni Pizza',
                    'Rocket Fuel Cola',
                ],
            ],
            [
                'reference' => 'PP-CLAW-002',
                'fulfillment_type' => OrderFulfillmentType::Pickup,
                'items' => [
                    'The Claw Calzone',
                    'Three-Eyed Cheese Pizza',
                    'Asteroid Fries',
                ],
            ],
            [
                'reference' => 'PP-BUZZ-003',
                'fulfillment_type' => OrderFulfillmentType::Delivery,
                'items' => [
                    'To Infinity and Pesto Pizza',
                    'Space Ranger Wings',
                    'Nebula Brownie Stack',
                ],
            ],
            [
                'reference' => 'PP-WOODY-004',
                'fulfillment_type' => OrderFulfillmentType::Pickup,
                'items' => [
                    'Sheriff Supreme Pizza',
                    'Rootin Tootin Mozzarella Sticks',
                    'Yeehaw Lemonade',
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $order = OrderModel::query()->updateOrCreate(
                attributes: ['reference' => $orderData['reference']],
                values: [
                    'fulfillment_type' => $orderData['fulfillment_type'],
                    'status' => OrderStatus::Pending,
                ],
            );

            foreach ($orderData['items'] as $itemName) {
                OrderItemModel::query()->updateOrCreate(
                    attributes: [
                        'order_id' => $order->id,
                        'name' => $itemName,
                    ],
                    values: ['status' => OrderItemStatus::Pending],
                );
            }
        }
    }
}
