<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_item_id', 'from_status', 'to_status'])]
class ItemStatusEvent extends Model
{
}
