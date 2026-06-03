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
            'status' => [
                'required',
                'string',
                Rule::enum(OrderItemStatus::class),
            ],
        ];
    }

    public function status(): OrderItemStatus
    {
        return OrderItemStatus::from($this->string(key: 'status')->toString());
    }
}
