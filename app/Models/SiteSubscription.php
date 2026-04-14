<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSubscription extends Model
{
    protected $fillable = [
        'is_current',
        'plan_slug',
        'subscription_status',
        'starts_at',
        'ends_at',
        'features',
        'limits',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'features' => 'array',
        'limits' => 'array',
    ];
}
