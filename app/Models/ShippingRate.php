<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = ['governorate', 'fee', 'free_shipping_threshold', 'is_active'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'free_shipping_threshold' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function feeFor(string $subtotal): string
    {
        return $this->free_shipping_threshold && bccomp($subtotal, (string) $this->free_shipping_threshold, 2) >= 0 ? '0.00' : (string) $this->fee;
    }
}
