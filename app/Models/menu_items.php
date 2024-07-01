<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class menu_items extends Model
{
    protected $fillable=[
        "id","resturant_id", "name","description","price","img","quantity"
    ];
}
