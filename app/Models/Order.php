<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];

    protected $fillable = [
        'user_id',
        'order_number',
        'checkout_token',
        'status',
        'subtotal',
        'total',
        'customer_name',
        'customer_email',
        'phone',
        'address',
        'guest_access_token', 'coupon_code', 'coupon_discount', 'shipping_fee',
        'points_redeemed', 'points_discount', 'points_earned', 'points_earned_at',
        'points_reversed_at', 'points_redemption_restored_at',
        'cancellation_requested_at', 'cancellation_reason', 'cancellation_status',
        'cancellation_reviewed_at', 'cancellation_reviewed_by',
    ];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'total' => 'decimal:2', 'coupon_discount' => 'decimal:2', 'points_discount' => 'decimal:2', 'shipping_fee' => 'decimal:2', 'points_redeemed' => 'integer', 'points_earned' => 'integer', 'points_earned_at' => 'datetime', 'points_reversed_at' => 'datetime', 'points_redemption_restored_at' => 'datetime', 'cancellation_requested_at' => 'datetime', 'cancellation_reviewed_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cancellationReviewer()
    {
        return $this->belongsTo(User::class, 'cancellation_reviewed_by')->withTrashed();
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function returnExchangeRequests()
    {
        return $this->hasMany(ReturnExchangeRequest::class);
    }

    public function canRequestCancellation(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true) && $this->cancellation_status === null;
    }
}
