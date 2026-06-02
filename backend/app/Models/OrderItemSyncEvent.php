<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'item_status_event_id',
    'destination_url',
    'payload',
    'status',
    'attempts',
    'last_attempted_at',
    'delivered_at',
    'last_error',
    'response_status',
])]
class OrderItemSyncEvent extends Model
{
}
