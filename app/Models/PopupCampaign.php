<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupCampaign extends Model
{
    protected $fillable = [
        'is_active',
        'title',
        'description',
        'image',
        'button_text',
        'button_url',
        'show_once_per_user',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_once_per_user' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}