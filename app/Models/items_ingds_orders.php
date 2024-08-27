<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class items_ingds_orders extends Model
{
    protected $fillable=[
        "id", "itemid","IngredientID"
    ];
}
