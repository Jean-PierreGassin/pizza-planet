<?php

namespace App\Exceptions;

use App\Enums\OrderItemStatus;
use RuntimeException;

class InvalidOrderItemStatusTransition extends RuntimeException
{
    public static function fromStatuses(OrderItemStatus $from, OrderItemStatus $to): self
    {
        return new self("Order item status cannot move from $from->value to $to->value.");
    }
}
