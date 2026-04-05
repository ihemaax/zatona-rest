<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalMenuSetting extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'logo',
        'banner',
        'phone',
        'address',
        'show_prices',
        'show_descriptions',
        'is_active',
    ];

    protected $casts = [
        'show_prices' => 'boolean',
        'show_descriptions' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(DigitalMenuCategory::class)->orderBy('sort_order');
    }
}