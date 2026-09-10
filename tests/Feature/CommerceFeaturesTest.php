<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'Rackets', 'slug' => 'rackets']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Court Racket',
            'slug' => 'court-racket',
            'price' => 1000,
            'discount_percent' => 0,
            'stock' => 5,
            'is_active' => true,
        ], $attributes));
    }

    private function purchase(User $user, Product $product, string $status = 'completed'): Order
    {
        $order = $user->orders()->create([
            'order_number' => 'ORD-'.str()->random(10),
            'status' => $status,
            'subtotal' => 1000,
            'total' => 1000,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'phone' => '01000000000',
            'address' => 'Cairo',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'subtotal' => 1000,
        ]);

        return $order;
    }

    public function test_admin_can_update_discount_and_customer_cannot(): void
    {
        $product = $this->product();
        $payload = ['name' => $product->name, 'category_id' => $product->category_id, 'price' => 1000, 'discount_percent' => 20, 'stock' => 5, 'is_active' => 1];

        $this->actingAs(User::factory()->create())->put(route('admin.products.update', $product), $payload)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.products.update', $product), $payload)->assertRedirect(route('admin.products.index'));

        $this->assertSame('20.00', $product->fresh()->discount_percent);
        $this->assertSame('800.00', $product->fresh()->finalPrice());
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.products.update', $product), array_merge($payload, ['discount_percent' => 101]))->assertSessionHasErrors('discount_percent');
    }

    public function test_checkout_uses_current_discount_and_preserves_order_item_price(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['discount_percent' => 25]);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->post(route('checkout.store'), [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'phone' => '01000000000',
            'governorate' => 'Cairo',
            'city_area' => 'Nasr City',
            'street' => 'Makram Ebeid Street',
            'building_number' => '10',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['total' => 1500]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'price' => 750, 'subtotal' => 1500]);
        $this->get(route('products.index'))->assertSee('25% off');
        $this->get(route('products.show', $product))->assertSee('1,000.00 EGP')->assertSee('750.00 EGP')->assertSee('25% off');
    }

    public function test_order_details_show_current_product_image_and_payment_method(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['image' => 'products/court-racket.png']);
        $order = $this->purchase($user, $product);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('products/court-racket.png')
            ->assertSee('Cash on delivery');
    }

    public function test_guest_cannot_review_and_purchaser_can_review_once(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $this->purchase($user, $product);

        $this->post(route('reviews.store', $product), ['rating' => 5])->assertRedirect(route('login'));
        $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5, 'comment' => 'Excellent control.'])->assertSessionHasNoErrors();
        $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 4])->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_review_rating_is_validated_and_cancelled_order_is_not_eligible(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $this->purchase($user, $product, 'cancelled');

        $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 6])->assertForbidden();
        $eligible = User::factory()->create();
        $this->purchase($eligible, $product);
        $this->actingAs($eligible)->post(route('reviews.store', $product), ['rating' => 6])->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_user_cannot_edit_another_review_and_admin_can_delete_it(): void
    {
        $product = $this->product();
        $owner = User::factory()->create();
        $review = Review::create(['user_id' => $owner->id, 'product_id' => $product->id, 'rating' => 4, 'comment' => 'Reliable.']);

        $this->actingAs(User::factory()->create())->patch(route('reviews.update', $review), ['rating' => 1])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->delete(route('admin.reviews.destroy', $review))->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_out_of_stock_state_is_visible_and_cannot_be_added(): void
    {
        $product = $this->product(['stock' => 0]);

        $this->get(route('products.index'))->assertSee('Out of stock');
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertSessionHasErrors('quantity');
    }

    public function test_policies_page_loads(): void
    {
        $this->get(route('policies'))->assertOk()->assertSee('Shipping')->assertSee('Returns')->assertSee('Exchanges');
    }
}
