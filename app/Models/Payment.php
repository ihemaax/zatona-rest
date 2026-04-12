<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'provider_reference',
        'payment_key',
        'status',
        'amount',
        'currency',
        'callback_payload',
        'webhook_payload',
        'paid_at',
        'failure_reason',
    ];

    protected $casts = [
        'callback_payload' => 'array',
        'webhook_payload' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
