<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use App\Support\VerificationRequirement;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    private ?Collection $cachedWishlistProductIds = null;

    public function sendEmailVerificationNotification(): void
    {
        if (app(VerificationRequirement::class)->required()) {
            $this->notify(new VerifyEmail);
        }
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function wishlistProductIds(): Collection
    {
        return $this->cachedWishlistProductIds ??= $this->wishlist()->pluck('products.id');
    }

    public function hasPurchasedProduct(Product $product): bool
    {
        return $this->orders()
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
