<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'admin_note',
        'updated_by_user_id',
        'last_action',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'features' => 'array',
        'limits' => 'array',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
