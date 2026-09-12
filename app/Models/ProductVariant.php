<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'name', 'attributes', 'price', 'stock', 'is_active'];

    protected function casts(): array
    {
        return ['attributes' => 'array', 'price' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function effectivePrice(): string
    {
        $base = $this->price ?? $this->product->price;
        $discount = bcdiv(bcmul((string) $base, (string) $this->product->discount_percent, 4), '100', 4);

        return bcsub((string) $base, $discount, 2);
    }
}
