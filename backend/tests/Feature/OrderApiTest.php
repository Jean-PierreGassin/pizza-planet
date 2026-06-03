<?php

namespace Tests\Feature;

use App\Enums\OrderFulfillmentType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function testOrderIndexRequiresAuthentication(): void
    {
        $response = $this->getJson('/api/v1/orders');

        $response->assertUnauthorized();
    }

    public function testOrderDetailRequiresAuthentication(): void
    {
        $response = $this->getJson('/api/v1/orders/1');

        $response->assertUnauthorized();
    }

    public function testOrderIndexReturnsAllOrdersWithItems(): void
    {
        $user = UserModel::factory()->create();
        $firstOrder = OrderModel::factory()->create([
            'reference' => 'PP-MOON-001',
            'fulfillment_type' => OrderFulfillmentType::Delivery,
            'status' => OrderStatus::Pending,
        ]);
        $secondOrder = OrderModel::factory()->create([
            'reference' => 'PP-CLAW-002',
            'fulfillment_type' => OrderFulfillmentType::Pickup,
            'status' => OrderStatus::Pending,
        ]);

        OrderItemModel::factory()->for($firstOrder, 'order')->create([
            'name' => 'Galactic Garlic Knots',
            'status' => OrderItemStatus::Pending,
        ]);
        OrderItemModel::factory()->for($secondOrder, 'order')->create([
            'name' => 'The Claw Calzone',
            'status' => OrderItemStatus::Pending,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonCount(2, 'orders')
            ->assertJsonPath('orders.0.reference', 'PP-MOON-001')
            ->assertJsonPath('orders.0.status', OrderStatus::Pending->value)
            ->assertJsonPath('orders.0.items.0.name', 'Galactic Garlic Knots')
            ->assertJsonPath('orders.0.items.0.status', OrderItemStatus::Pending->value)
            ->assertJsonPath('orders.1.reference', 'PP-CLAW-002');
    }

    public function testOrderDetailReturnsSelectedOrderWithItems(): void
    {
        $user = UserModel::factory()->create();
        $order = OrderModel::factory()->create([
            'reference' => 'PP-BUZZ-003',
            'fulfillment_type' => OrderFulfillmentType::Delivery,
            'status' => OrderStatus::Pending,
        ]);

        OrderItemModel::factory()->for($order, 'order')->create([
            'name' => 'To Infinity and Pesto Pizza',
            'status' => OrderItemStatus::Pending,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('order.id', $order->id)
            ->assertJsonPath('order.reference', 'PP-BUZZ-003')
            ->assertJsonPath('order.fulfillment_type', OrderFulfillmentType::Delivery->value)
            ->assertJsonPath('order.items.0.name', 'To Infinity and Pesto Pizza');
    }
}
