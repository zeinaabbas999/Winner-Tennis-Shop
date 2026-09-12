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
        'is_featured',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'discount_percent' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean', 'is_featured' => 'boolean', 'specs' => 'array'];
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

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function wishlistUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function inventoryAdjustments()
    {
        return $this->hasMany(InventoryAdjustment::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.ltrim($this->image, '/')) : null;
    }

    public function badges(int $lowStockThreshold = 5): array
    {
        $stock = $this->effectiveStock();

        return array_values(array_filter([
            $this->hasDiscount() ? number_format((float) $this->discount_percent).'% off' : null,
            $stock > 0 && $stock <= $lowStockThreshold ? 'Limited stock' : null,
            $this->created_at?->isAfter(now()->subDays(30)) ? 'New' : null,
            ($this->sales_quantity ?? 0) >= 5 ? 'Best seller' : null,
        ]));
    }

    public function effectiveStock(): int
    {
        if (array_key_exists('variants_stock', $this->attributes) && $this->variants_stock !== null) {
            return (int) $this->variants_stock;
        }
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (int) $this->variants->where('is_active', true)->sum('stock');
        }

        return $this->stock;
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
