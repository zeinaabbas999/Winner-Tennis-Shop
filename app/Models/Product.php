<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand',
        'name',
        'slug',
        'description',
        'specs',
        'price',
        'discount_percent',
        'stock',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'discount_percent' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean', 'specs' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function hasDiscount(): bool
    {
        return bccomp((string) $this->discount_percent, '0', 2) === 1;
    }

    public function finalPrice(): string
    {
        if (! $this->hasDiscount()) {
            return number_format((float) $this->price, 2, '.', '');
        }

        $discount = bcdiv(bcmul((string) $this->price, (string) $this->discount_percent, 4), '100', 4);

        return bcsub((string) $this->price, $discount, 2);
    }
}
