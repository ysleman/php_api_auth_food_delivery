<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class order_items extends Model
{
    protected $fillable=[
        "order_id", "item_id","quanity","resturant_id"
    ];
}
