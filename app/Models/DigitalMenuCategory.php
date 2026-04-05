<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalMenuCategory extends Model
{
    protected $fillable = [
        'digital_menu_setting_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setting()
    {
        return $this->belongsTo(DigitalMenuSetting::class, 'digital_menu_setting_id');
    }

    public function items()
    {
        return $this->hasMany(DigitalMenuItem::class)->orderBy('sort_order');
    }
}