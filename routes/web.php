<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PolicyController as AdminPolicyController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ShippingRateController as AdminShippingRateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GuestOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\OrderCancellationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/shop', [ProductController::class, 'index'])->name('products.index');
Route::get('/shop/accessories', [ProductController::class, 'accessories'])->name('shop.accessories');
Route::get('/shop/{category}', [ProductController::class, 'category'])->name('shop.category');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::view('/racket-guide', 'pages.racket-guide')->name('racket-guide');
Route::view('/services', 'pages.services')->name('services');
Route::view('/contact', 'pages.contact')->name('contact');
Route::get('/policies', [LegalController::class, 'policies'])->name('policies');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/cookies', [LegalController::class, 'cookies'])->name('cookies');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->middleware('throttle:10,1')->name('cart.coupon.store');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.destroy');
Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
Route::get('/guest/orders/{order}/{token}', [GuestOrderController::class, 'show'])->middleware('throttle:20,1')->name('guest.orders.show');

Route::redirect('/dashboard', '/orders')->middleware(['auth', 'customer.verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->middleware('throttle:30,1')->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->middleware('throttle:30,1')->name('wishlist.destroy');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
    Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->middleware('throttle:10,1')->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->middleware('throttle:10,1')->name('reviews.destroy');
});

Route::middleware(['auth', 'customer.verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancellation', [OrderCancellationController::class, 'store'])->middleware('throttle:5,1')->name('orders.cancellation.store');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');
        Route::resource('products', AdminProductController::class)->except('show');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::patch('orders/{order}/cancellation', [AdminOrderController::class, 'reviewCancellation'])->name('orders.cancellation.review');
        Route::get('inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
        Route::patch('inventory/{product}', [AdminInventoryController::class, 'store'])->name('inventory.update');
        Route::resource('coupons', AdminCouponController::class)->except('show');
        Route::resource('shipping-rates', AdminShippingRateController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('products/{product}/variants', [AdminProductVariantController::class, 'store'])->name('products.variants.store');
        Route::patch('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
        Route::post('users/{user}/points', [AdminUserController::class, 'adjustPoints'])->name('users.points.store');
        Route::get('policies', [AdminPolicyController::class, 'index'])->name('policies.index');
        Route::get('policies/{policyPage}/edit', [AdminPolicyController::class, 'edit'])->name('policies.edit');
        Route::put('policies/{policyPage}', [AdminPolicyController::class, 'update'])->name('policies.update');
        Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';
