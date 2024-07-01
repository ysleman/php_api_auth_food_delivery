<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class temp_orders extends Model
{
    protected $fillable=[
        "user_id", "item_id","quanity"
    ];
}
