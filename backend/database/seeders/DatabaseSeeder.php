<?php

namespace Database\Seeders;

use App\Enums\OrderFulfillmentType;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
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
        UserModel::factory()
            ->mario()
            ->create();

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
            $factory = OrderModel::factory()
                ->pending()
                ->state(['reference' => $orderData['reference']]);

            $factory = $orderData['fulfillment_type'] === OrderFulfillmentType::Delivery
                ? $factory->delivery()
                : $factory->pickup();

            $order = $factory->create();

            foreach ($orderData['items'] as $itemName) {
                OrderItemModel::factory()
                    ->for($order, 'order')
                    ->named($itemName)
                    ->pending()
                    ->create();
            }
        }
    }
}
