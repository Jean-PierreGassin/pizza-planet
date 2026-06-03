<?php

namespace App\Http\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'orders' => $this->orders->allWithItems()
                ->map(fn (OrderModel $order): array => $this->orderPayload($order))
                ->values(),
        ]);
    }

    public function show(int $order): JsonResponse
    {
        return response()->json([
            'order' => $this->orderPayload($this->orders->findWithItems($order)),
        ]);
    }

    private function orderPayload(OrderModel $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'fulfillment_type' => $order->fulfillment_type->value,
            'status' => $order->status->value,
            'items' => $order->items
                ->map(fn (OrderItemModel $item): array => $this->itemPayload($item))
                ->values(),
        ];
    }

    private function itemPayload(OrderItemModel $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'status' => $item->status->value,
        ];
    }
}
