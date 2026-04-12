<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'delivery_user_id',
        'assigned_to_delivery_at',
        'out_for_delivery_at',
        'delivered_at',
        'order_type',
        'branch_id',
        'coupon_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'address_line',
        'area',
        'latitude',
        'longitude',
        'notes',
        'coupon_code',
        'subtotal',
        'discount_amount',
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
        'assigned_to_delivery_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
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

    public function deliveryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}
