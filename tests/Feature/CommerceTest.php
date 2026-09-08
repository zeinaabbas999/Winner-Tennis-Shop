<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create(['name' => 'Dresses', 'slug' => 'dresses']);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge(['category_id' => $this->category()->id, 'name' => 'Silk Dress', 'slug' => 'silk-dress', 'price' => 1200, 'stock' => 5, 'is_active' => true], $attributes));
    }

    public function test_customer_is_denied_admin_and_admin_is_allowed(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin')->assertOk();
    }

    public function test_only_active_products_are_public(): void
    {
        $active = $this->product();
        $inactive = Product::create(['category_id' => $active->category_id, 'name' => 'Hidden', 'slug' => 'hidden', 'price' => 10, 'stock' => 1, 'is_active' => false]);
        $this->get(route('products.index'))->assertSee($active->name)->assertDontSee($inactive->name);
        $this->get(route('products.show', $inactive))->assertNotFound();
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $product = $this->product(['stock' => 2]);
        $this->post(route('cart.store', $product), ['quantity' => 3])->assertSessionHasErrors('quantity');
    }

    public function test_checkout_creates_order_items_reduces_stock_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => 250, 'stock' => 5]);
        $response = $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->post(route('checkout.store'), ['customer_name' => 'Customer', 'customer_email' => 'buyer@example.com', 'phone' => '01000000000', 'address' => 'Cairo, Egypt']);
        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order))->assertSessionMissing('cart');
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 500]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'subtotal' => 500]);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_revalidates_stock_without_partial_order(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 1]);
        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->post(route('checkout.store'), ['customer_name' => 'Customer', 'customer_email' => 'buyer@example.com', 'phone' => '01000000000', 'address' => 'Cairo'])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $owner->orders()->create(['order_number' => 'ORD-TEST', 'status' => 'pending', 'subtotal' => 10, 'total' => 10, 'customer_name' => 'Owner', 'customer_email' => $owner->email, 'phone' => '1', 'address' => 'Cairo']);
        $this->actingAs($other)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = $this->product();
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $product->category))->assertSessionHas('warning');
        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
    }

    public function test_admin_product_validation_and_unique_slug_generation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();
        $this->actingAs($admin)->post(route('admin.products.store'), ['name' => '', 'category_id' => 999, 'price' => -1, 'stock' => -1])->assertSessionHasErrors(['name', 'category_id', 'price', 'stock']);
        foreach ([1, 2] as $index) {
            $this->actingAs($admin)->post(route('admin.products.store'), ['name' => 'Classic Coat', 'category_id' => $category->id, 'price' => 100, 'stock' => 1, 'is_active' => 1])->assertRedirect(route('admin.products.index'));
        }
        $this->assertDatabaseHas('products', ['slug' => 'classic-coat']);
        $this->assertDatabaseHas('products', ['slug' => 'classic-coat-2']);
    }

    public function test_category_page_only_displays_products_from_that_category(): void
    {
        $rackets = $this->category();
        $balls = Category::create(['name' => 'Balls', 'slug' => 'balls']);
        Product::create(['category_id' => $rackets->id, 'brand' => 'Wilson', 'name' => 'Blade', 'slug' => 'blade', 'price' => 200, 'stock' => 2, 'is_active' => true]);
        Product::create(['category_id' => $balls->id, 'brand' => 'Dunlop', 'name' => 'Fort Ball', 'slug' => 'fort-ball', 'price' => 20, 'stock' => 2, 'is_active' => true]);

        $this->get(route('shop.category', $rackets))->assertOk()->assertSee('Blade')->assertDontSee('Fort Ball');
    }

    public function test_search_matches_brand_and_brand_filter_is_restricted(): void
    {
        $this->product(['brand' => 'Wilson']);
        $this->get(route('products.index', ['search' => 'Wilson']))->assertOk()->assertSee('Silk Dress');
        $this->get(route('products.index', ['brand' => 'Tecnifibre']))->assertSessionHasErrors('brand');
    }

    public function test_checkout_page_displays_server_calculated_summary(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => 250, 'stock' => 5]);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])
            ->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('500.00 EGP')
            ->assertSee($product->name);
    }
}
