<?php

namespace Tests\Feature;

use App\Mail\OrderStatusChanged;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommerceExpansionTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'rackets'], ['name' => 'Rackets']);

        return Product::create(array_merge(['category_id' => $category->id, 'name' => 'Test Racket', 'slug' => 'test-racket-'.str()->random(5), 'price' => 1000, 'discount_percent' => 0, 'stock' => 5, 'is_active' => true], $attributes));
    }

    private function checkout(string $token = '18362427-4f5b-46ac-b81c-fce80da63cef'): array
    {
        return ['checkout_token' => $token, 'customer_name' => 'Customer', 'customer_email' => 'buyer@example.com', 'phone' => '01000000000', 'governorate' => 'Cairo', 'city_area' => 'Nasr City', 'street' => 'Court Street', 'building_number' => '10'];
    }

    public function test_wishlist_is_unique_and_owned_by_authenticated_user(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->post(route('wishlist.store', $product))->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('wishlist.store', $product))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('wishlists', 1);
        $this->actingAs($other)->delete(route('wishlist.destroy', $product));
        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_related_products_exclude_current_and_inactive_products(): void
    {
        $current = $this->product(['name' => 'Current']);
        $related = $this->product(['name' => 'Related']);
        $hidden = $this->product(['name' => 'Hidden Related', 'is_active' => false]);
        $response = $this->get(route('products.show', $current))->assertOk()->assertSee($related->name)->assertDontSee($hidden->name);
        $this->assertFalse($response->viewData('related')->contains($current));
    }

    public function test_featured_products_are_admin_managed_and_inactive_are_hidden(): void
    {
        $active = $this->product(['name' => 'Featured Active', 'is_featured' => true]);
        $this->product(['name' => 'Featured Hidden', 'is_featured' => true, 'is_active' => false]);
        $this->get(route('home'))->assertSee($active->name)->assertDontSee('Featured Hidden');
        $this->actingAs(User::factory()->create())->put(route('admin.products.update', $active), [])->assertForbidden();
    }

    public function test_inventory_adjustment_is_recorded_and_cannot_be_negative(): void
    {
        $product = $this->product();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('admin.inventory.update', $product), ['quantity' => -1])->assertSessionHasErrors('quantity');
        $this->actingAs($admin)->patch(route('admin.inventory.update', $product), ['quantity' => 4])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('inventory_adjustments', ['product_id' => $product->id, 'quantity_before' => 5, 'quantity_after' => 9, 'quantity_change' => 4, 'reason' => 'restock']);
    }

    public function test_cancellation_restores_stock_only_once(): void
    {
        Mail::fake();
        $product = $this->product(['stock' => 3]);
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $user->orders()->create(['order_number' => 'ORD-CANCEL', 'status' => 'pending', 'subtotal' => 1000, 'total' => 1000, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo']);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'price' => 1000, 'quantity' => 2, 'subtotal' => 2000]);
        $this->actingAs($user)->post(route('orders.cancellation.store', $order), ['reason' => 'Changed plans'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.orders.cancellation.review', $order), ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.orders.cancellation.review', $order), ['decision' => 'approved'])->assertStatus(422);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(1, InventoryAdjustment::where('reason', 'cancellation/restoration')->count());
    }

    public function test_shipped_order_cannot_request_cancellation(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create(['order_number' => 'ORD-SHIPPED', 'status' => 'shipped', 'subtotal' => 10, 'total' => 10, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo']);
        $this->actingAs($user)->post(route('orders.cancellation.store', $order), ['reason' => 'Too late'])->assertStatus(422);
        $this->assertNull($order->fresh()->cancellation_requested_at);
    }

    public function test_status_email_is_queued_only_when_status_changes(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $user->orders()->create(['order_number' => 'ORD-STATUS', 'status' => 'pending', 'subtotal' => 10, 'total' => 10, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo']);
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'pending']);
        Mail::assertNothingQueued();
        $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'confirmed']);
        Mail::assertQueued(OrderStatusChanged::class, 1);
    }

    public function test_variant_coupon_shipping_and_guest_checkout_are_server_authoritative(): void
    {
        Mail::fake();
        $product = $this->product(['price' => 1000]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'GRIP-3', 'name' => 'Grip 3', 'attributes' => ['grip_size' => '3'], 'price' => 1200, 'stock' => 2, 'is_active' => true]);
        Coupon::create(['code' => 'COURT10', 'type' => 'percentage', 'value' => 10, 'minimum_order_amount' => 500, 'is_active' => true]);
        ShippingRate::create(['governorate' => 'Cairo', 'fee' => 75, 'free_shipping_threshold' => 2000, 'is_active' => true]);
        $token = $this->checkout()['checkout_token'];
        $response = $this->withSession(['cart' => [$product->id.':'.$variant->id => 1], 'coupon_code' => 'COURT10', 'checkout_tokens' => [$token]])->post(route('checkout.store'), $this->checkout());
        $order = Order::firstOrFail();
        $response->assertRedirect();
        $this->assertNull($order->user_id);
        $this->assertSame('120.00', $order->coupon_discount);
        $this->assertSame('75.00', $order->shipping_fee);
        $this->assertSame('1155.00', $order->total);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_variant_id' => $variant->id, 'variant_sku' => 'GRIP-3', 'price' => 1200]);
        $this->assertSame(1, $variant->fresh()->stock);
        $this->get(route('guest.orders.show', [$order, 'wrong-token']))->assertNotFound();
    }

    public function test_variant_must_belong_to_product_and_variant_stock_is_enforced(): void
    {
        $product = $this->product();
        $other = $this->product();
        $variant = ProductVariant::create(['product_id' => $other->id, 'sku' => 'OTHER-1', 'name' => 'Other option', 'stock' => 1, 'is_active' => true]);
        $this->post(route('cart.store', $product), ['quantity' => 1, 'variant_id' => $variant->id])->assertSessionHasErrors('variant_id');
        $this->post(route('cart.store', $other), ['quantity' => 2, 'variant_id' => $variant->id])->assertSessionHasErrors('quantity');
    }

    public function test_coupon_windows_minimums_limits_and_free_shipping_are_enforced(): void
    {
        $expired = Coupon::create(['code' => 'OLD', 'type' => 'fixed', 'value' => 100, 'minimum_order_amount' => 0, 'is_active' => true, 'expires_at' => now()->subMinute()]);
        $inactive = Coupon::create(['code' => 'OFF', 'type' => 'fixed', 'value' => 100, 'minimum_order_amount' => 0, 'is_active' => false]);
        $limited = Coupon::create(['code' => 'USED', 'type' => 'fixed', 'value' => 100, 'minimum_order_amount' => 500, 'usage_limit' => 1, 'times_used' => 1, 'is_active' => true]);
        $this->assertFalse($expired->isAvailable('1000.00'));
        $this->assertFalse($inactive->isAvailable('1000.00'));
        $this->assertTrue($limited->isAvailable('1000.00'));
        $this->assertFalse(Coupon::create(['code' => 'MIN', 'type' => 'fixed', 'value' => 100, 'minimum_order_amount' => 1500, 'is_active' => true])->isAvailable('1000.00'));
        $rate = ShippingRate::create(['governorate' => 'Giza', 'fee' => 75, 'free_shipping_threshold' => 2000, 'is_active' => true]);
        $this->assertSame('0.00', $rate->feeFor('2000.00'));
        $this->assertSame('75.00', $rate->feeFor('1999.99'));
    }
}
