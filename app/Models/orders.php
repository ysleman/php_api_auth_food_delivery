<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    protected $fillable=[
        "user_id", "driver_id","OrderDate","totalPrice"
    ];
}
