<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'minimum_order_amount', 'usage_limit', 'per_user_limit', 'times_used', 'is_active', 'starts_at', 'expires_at'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'minimum_order_amount' => 'decimal:2', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function isAvailable(string $subtotal): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->expires_at || $this->expires_at->isFuture())
            && (! $this->usage_limit || $this->times_used < $this->usage_limit)
            && bccomp($subtotal, (string) $this->minimum_order_amount, 2) >= 0;
    }

    public function discountFor(string $subtotal): string
    {
        $amount = $this->type === 'percentage'
            ? bcdiv(bcmul($subtotal, (string) $this->value, 2), '100', 2)
            : (string) $this->value;

        return bccomp($amount, $subtotal, 2) === 1 ? $subtotal : number_format((float) $amount, 2, '.', '');
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}
