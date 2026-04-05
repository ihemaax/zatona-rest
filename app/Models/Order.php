<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_type',
        'branch_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'address_line',
        'area',
        'latitude',
        'longitude',
        'notes',
        'subtotal',
        'delivery_fee',
        'total',
        'payment_method',
        'status',
        'estimated_delivery_minutes',
        'estimated_delivery_at',
        'status_note',
        'is_seen_by_admin',
        'guest_token',
    ];

    protected $casts = [
        'estimated_delivery_at' => 'datetime',
        'is_seen_by_admin' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function ($order) {
            if (!$order->order_number) {
                $order->updateQuietly([
                    'order_number' => 'ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}