<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidOrderItemStatusTransition;
use App\Http\Requests\UpdateOrderItemStatusRequest;
use App\Services\OrderItemStatusTransitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderItemStatusController extends Controller
{
    public function __construct(
        private readonly OrderItemStatusTransitionService $statusTransitionService,
    ) {
    }

    public function update(UpdateOrderItemStatusRequest $request): JsonResponse
    {
        try {
            $result = $this->statusTransitionService->transition($request->toData());
        } catch (InvalidOrderItemStatusTransition $exception) {
            throw ValidationException::withMessages([
                'status' => [$exception->getMessage()],
            ]);
        }

        return response()->json([
            'order_item_id' => $result->orderItem->id,
            'status' => $result->status->value,
            'item_status_event_id' => $result->itemStatusEvent->id,
            'order_item_sync_event_id' => $result->syncEvent->id,
        ]);
    }
}
