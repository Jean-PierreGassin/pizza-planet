<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case ReadyForPickup = 'ready_for_pickup';
    case ReadyForDelivery = 'ready_for_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
