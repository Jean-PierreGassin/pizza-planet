<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Enums\SyncEventStatus;
use App\Models\ItemStatusEvent;
use App\Models\OrderItemSyncEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemSyncEvent>
 */
class OrderItemSyncEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_status_event_id' => ItemStatusEvent::factory(),
            'destination_url' => fake()->url(),
            'payload' => [
                'status' => OrderItemStatus::Preparing->value,
            ],
            'status' => SyncEventStatus::Pending,
            'attempts' => 0,
        ];
    }
}
