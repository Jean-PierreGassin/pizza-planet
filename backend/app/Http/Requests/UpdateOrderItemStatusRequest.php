<?php

namespace App\Http\Requests;

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

    public function orderId(): int
    {
        return $this->integer(key: 'order_id');
    }

    public function orderItemId(): int
    {
        return $this->integer(key: 'order_item_id');
    }

    public function status(): OrderItemStatus
    {
        return OrderItemStatus::from($this->string(key: 'status')->toString());
    }
}
