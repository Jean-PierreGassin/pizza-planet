<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidOrderItemStatusTransition;
use App\Http\Requests\UpdateOrderItemStatusRequest;
use App\Services\OrderItemStatusTransitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderItemController extends Controller
{
    public function __construct(
        private readonly OrderItemStatusTransitionService $statusTransitionService,
    ) {
    }

    public function update(UpdateOrderItemStatusRequest $request, int $order, int $item): JsonResponse
    {
        try {
            $result = $this->statusTransitionService->transition(
                orderId: $order,
                orderItemId: $item,
                status: $request->status(),
            );
        } catch (InvalidOrderItemStatusTransition $exception) {
            throw ValidationException::withMessages(messages: [
                'status' => [$exception->getMessage()],
            ]);
        }

        return response()->json(data: $result->toArray());
    }
}
