<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'restaurant_name',
        'restaurant_phone',
        'restaurant_address',
        'logo',
        'banner',
        'delivery_fee',
        'is_open',
    ];
}