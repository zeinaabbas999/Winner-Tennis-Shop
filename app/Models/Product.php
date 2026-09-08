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
        'stock',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean', 'specs' => 'array'];
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
}
