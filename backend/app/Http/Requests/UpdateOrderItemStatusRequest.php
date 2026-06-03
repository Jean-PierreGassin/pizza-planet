<?php

namespace App\Http\Requests;

use App\DTOs\UpdateOrderItemStatusDTO;
use App\Enums\OrderItemStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderItemStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],
            'order_item_id' => [
                'required',
                'integer',
                'exists:order_items,id',
            ],
            'status' => [
                'required',
                'string',
                Rule::enum(OrderItemStatus::class),
            ],
        ];
    }

    public function toData(): UpdateOrderItemStatusDTO
    {
        $validated = $this->validated();

        return new UpdateOrderItemStatusDTO(
            orderId: $validated['order_id'],
            orderItemId: $validated['order_item_id'],
            status: OrderItemStatus::from($validated['status']),
        );
    }
}
