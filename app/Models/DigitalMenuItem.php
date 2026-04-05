<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalMenuItem extends Model
{
    protected $fillable = [
        'digital_menu_category_id',
        'name',
        'description',
        'price',
        'image',
        'badge',
        'sort_order',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(DigitalMenuCategory::class, 'digital_menu_category_id');
    }
}