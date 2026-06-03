<?php

namespace Database\Factories;

use App\Enums\OrderItemStatus;
use App\Enums\SyncEventStatus;
use App\Enums\WebhookEventType;
use App\Models\OrderItemStatusEventModel;
use App\Models\WebhookSyncEventModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookSyncEventModel>
 */
class WebhookSyncEventModelFactory extends Factory
{
    protected $model = WebhookSyncEventModel::class;

    public function definition(): array
    {
        return [
            'order_item_status_event_id' => OrderItemStatusEventModel::factory(),
            'order_status_event_id' => null,
            'event_type' => WebhookEventType::OrderItemStatusUpdated,
            'destination_url' => fake()->url(),
            'payload' => [
                'status' => OrderItemStatus::Preparing->value,
            ],
            'status' => SyncEventStatus::Pending,
            'attempts' => 0,
        ];
    }
}
