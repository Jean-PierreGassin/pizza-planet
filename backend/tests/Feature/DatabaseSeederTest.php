<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\OrderModel;
use App\Models\UserModel;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function testSeederCreatesMarioAndInitialOrders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mario = UserModel::query()
            ->where('email', 'mario@pizzaplanet.test')
            ->firstOrFail();

        $this->assertSame('Mario', $mario->name);
        $this->assertTrue(Hash::check('ilovepizza', $mario->password));
        $this->assertSame(4, OrderModel::query()->count());

        OrderModel::query()
            ->with('items')
            ->get()
            ->each(function (OrderModel $order): void {
                $this->assertSame(OrderStatus::Pending, $order->status);
                $this->assertNotEmpty($order->items);

                $order->items->each(
                    fn ($item) => $this->assertSame(OrderItemStatus::Pending, $item->status),
                );
            });
    }

    public function testSeederCanRunMoreThanOnce(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, UserModel::query()->where('email', 'mario@pizzaplanet.test')->count());
        $this->assertSame(4, OrderModel::query()->count());

        OrderModel::query()
            ->with('items')
            ->get()
            ->each(function (OrderModel $order): void {
                $this->assertNotEmpty($order->items);
            });
    }
}
