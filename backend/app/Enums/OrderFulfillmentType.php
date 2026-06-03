<?php

namespace App\Enums;

enum OrderFulfillmentType: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';
}
