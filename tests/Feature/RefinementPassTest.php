<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefinementPassTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_limit_caps_percentage_and_fixed_discounts(): void
    {
        $percentage = Coupon::create(['code' => 'CAP20', 'type' => 'percentage', 'value' => 20, 'minimum_order_amount' => 0, 'max_discount_amount' => 2000, 'is_active' => true]);
        $fixed = Coupon::create(['code' => 'FIXED', 'type' => 'fixed', 'value' => 500, 'minimum_order_amount' => 0, 'max_discount_amount' => 250, 'is_active' => true]);
        $uncapped = Coupon::create(['code' => 'OPEN', 'type' => 'percentage', 'value' => 20, 'minimum_order_amount' => 0, 'is_active' => true]);

        $this->assertSame('2000.00', $percentage->discountFor('20000.00'));
        $this->assertSame('1000.00', $percentage->discountFor('5000.00'));
        $this->assertSame('250.00', $fixed->discountFor('1000.00'));
        $this->assertSame('4000.00', $uncapped->discountFor('20000.00'));
    }

    public function test_admin_can_only_restock_with_a_positive_quantity(): void
    {
        $product = $this->product();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('admin.inventory.update', $product), ['quantity' => 0])->assertSessionHasErrors('quantity');
        $this->actingAs($admin)->patch(route('admin.inventory.update', $product), ['quantity' => -5])->assertSessionHasErrors('quantity');
        $this->actingAs($admin)->patch(route('admin.inventory.update', $product), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertDatabaseHas('inventory_adjustments', ['product_id' => $product->id, 'quantity_change' => 3, 'reason' => 'restock']);
        $this->actingAs(User::factory()->create())->patch(route('admin.inventory.update', $product), ['quantity' => 1])->assertForbidden();
    }

    public function test_category_specific_specs_are_validated_and_saved_without_variants(): void
    {
        $category = Category::create(['name' => 'Tennis Rackets', 'slug' => 'tennis-rackets']);
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = ['name' => 'Structured Racket', 'category_id' => $category->id, 'brand' => 'Wilson', 'description' => 'Test', 'price' => 1000, 'discount_percent' => 0, 'stock' => 2, 'is_active' => 1, 'is_featured' => 0, 'new_badge_mode' => 'auto', 'specs' => ['head_size' => '100 sq in', 'length' => '27 in', 'play_style' => 'All-Court']];

        $this->actingAs($admin)->post(route('admin.products.store'), $payload)->assertRedirect(route('admin.products.index'));
        $product = Product::where('name', 'Structured Racket')->firstOrFail();
        $this->assertSame('100 sq in', $product->specs['head_size']);
        $this->assertSame('27 in', $product->specs['length']);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_supported_category_specifications_save_cleanly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $examples = [
            'tennis-balls' => ['ball_type' => 'Match', 'balls_per_can' => '3'],
            'tennis-bags' => ['racquet_capacity' => '6', 'compartments' => '3'],
            'strings' => ['string_type' => 'Polyester', 'gauge' => '1.25 mm'],
            'grips-and-overgrips' => ['grip_type' => 'Overgrip', 'pack_size' => '3'],
            'dampeners' => ['dampener_type' => 'Button', 'material' => 'Silicone'],
        ];

        foreach ($examples as $slug => $specs) {
            $category = Category::create(['name' => str($slug)->headline(), 'slug' => $slug]);
            $payload = ['name' => str($slug)->headline().' Product', 'category_id' => $category->id, 'brand' => 'Wilson', 'price' => 100, 'discount_percent' => 0, 'stock' => 2, 'is_active' => 1, 'is_featured' => 0, 'new_badge_mode' => 'auto', 'specs' => $specs];
            $this->actingAs($admin)->post(route('admin.products.store'), $payload)->assertRedirect(route('admin.products.index'));
            $this->assertEqualsCanonicalizing($specs, Product::where('category_id', $category->id)->firstOrFail()->specs);
        }
    }

    public function test_order_views_load_when_product_image_is_missing(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product(['image' => null]);
        $order = $user->orders()->create(['order_number' => 'ORD-IMAGE', 'status' => 'pending', 'subtotal' => 100, 'total' => 100, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo']);
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'price' => 100, 'quantity' => 1, 'subtotal' => 100]);

        $this->actingAs($user)->get(route('orders.show', $order))->assertOk()->assertSee('WINNER');
        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->assertSee('WINNER');
    }

    public function test_historical_order_coupon_snapshot_is_not_recalculated(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create(['order_number' => 'ORD-SNAPSHOT', 'status' => 'pending', 'subtotal' => 1000, 'coupon_code' => 'OLD', 'coupon_discount' => 300, 'total' => 700, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo']);
        Coupon::create(['code' => 'OLD', 'type' => 'percentage', 'value' => 50, 'minimum_order_amount' => 0, 'max_discount_amount' => 100, 'is_active' => true]);

        $this->assertSame('300.00', $order->fresh()->coupon_discount);
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'rackets'], ['name' => 'Rackets']);

        return Product::create(array_merge(['category_id' => $category->id, 'name' => 'Test Product', 'slug' => 'test-'.str()->random(8), 'price' => 100, 'discount_percent' => 0, 'stock' => 5, 'is_active' => true], $attributes));
    }
}
