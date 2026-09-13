<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirstOrderOffer extends Model
{
    protected $fillable = ['user_id', 'reward_type', 'reward_value', 'claimed_at', 'used_at', 'order_id'];

    protected function casts(): array
    {
        return ['reward_value' => 'decimal:2', 'claimed_at' => 'datetime', 'used_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function label(): string
    {
        return match ($this->reward_type) {
            'percentage' => rtrim(rtrim($this->reward_value, '0'), '.').'% off',
            'fixed' => number_format((float) $this->reward_value, 0).' EGP off',
            'free_shipping' => 'Free shipping',
            default => 'First-order offer',
        };
    }
}
