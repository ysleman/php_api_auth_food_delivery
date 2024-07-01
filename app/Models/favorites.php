<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class favorites extends Model
{
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','resturant_id'
    ];
}
