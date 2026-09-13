<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerQuestion;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnExchangeRequest;
use App\Models\ShippingRate;
use App\Models\User;
use App\Support\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlledOperationsPassTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_category_terms_and_excludes_irrelevant_and_inactive_products(): void
    {
        $bags = Category::create(['name' => 'Tennis Bags', 'slug' => 'tennis-bags']);
        $grips = Category::create(['name' => 'Overgrips', 'slug' => 'grips-and-overgrips']);
        $other = Category::create(['name' => 'Other', 'slug' => 'other']);
        Product::create(['category_id' => $bags->id, 'name' => 'Wilson Team Six Bag', 'slug' => 'team-six-bag', 'price' => 100, 'stock' => 2, 'is_active' => true]);
        Product::create(['category_id' => $grips->id, 'name' => 'Wilson Pro Grip', 'slug' => 'pro-grip', 'price' => 20, 'stock' => 2, 'is_active' => true]);
        Product::create(['category_id' => $other->id, 'name' => 'Court Towel', 'slug' => 'court-towel', 'description' => 'Fits in tennis bags', 'price' => 20, 'stock' => 2, 'is_active' => true]);
        Product::create(['category_id' => $bags->id, 'name' => 'Hidden Bag', 'slug' => 'hidden-bag', 'price' => 20, 'stock' => 2, 'is_active' => false]);

        $this->get(route('products.index', ['search' => 'bags']))->assertOk()->assertSee('Wilson Team Six Bag')->assertDontSee('Court Towel')->assertDontSee('Hidden Bag');
        $this->get(route('products.index', ['search' => 'overgrips']))->assertOk()->assertSee('Wilson Pro Grip');
    }

    public function test_global_shipping_threshold_is_admin_only_and_centralized(): void
    {
        $rate = ShippingRate::create(['governorate' => 'Cairo', 'fee' => 75, 'free_shipping_threshold' => 100, 'is_active' => true]);
        $customer = User::factory()->create();
        $this->actingAs($customer)->patch(route('admin.shipping-rates.threshold'), ['free_shipping_threshold' => 2000])->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('admin.shipping-rates.threshold'), ['free_shipping_threshold' => 2000])->assertRedirect();
        $this->assertSame('75.00', app(Shipping::class)->feeFor($rate, '1999.99'));
        $this->assertSame('0.00', app(Shipping::class)->feeFor($rate, '2000.00'));
        $this->assertDatabaseHas('application_settings', ['key' => Shipping::THRESHOLD_KEY, 'value' => '2000.00']);
    }

    public function test_customer_can_request_return_for_own_delivered_item_only(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order($owner, 'delivered');
        $item = $order->items()->create(['product_id' => $this->product()->id, 'product_name' => 'Racket', 'price' => 100, 'quantity' => 1, 'subtotal' => 100]);
        $payload = ['type' => 'return', 'order_item_id' => $item->id, 'reason' => 'Unused and no longer needed.'];

        $this->actingAs($other)->post(route('orders.returns.store', $order), $payload)->assertForbidden();
        $this->actingAs($owner)->post(route('orders.returns.store', $order), $payload)->assertRedirect();
        $this->assertDatabaseHas('return_exchange_requests', ['order_item_id' => $item->id, 'type' => 'return', 'status' => 'pending']);
    }

    public function test_admin_can_review_return_but_customer_cannot(): void
    {
        $owner = User::factory()->create();
        $order = $this->order($owner, 'delivered');
        $item = $order->items()->create(['product_id' => $this->product()->id, 'product_name' => 'Racket', 'price' => 100, 'quantity' => 1, 'subtotal' => 100]);
        $entry = ReturnExchangeRequest::create(['order_id' => $order->id, 'order_item_id' => $item->id, 'user_id' => $owner->id, 'type' => 'exchange', 'reason' => 'Different model requested.']);

        $this->actingAs($owner)->patch(route('admin.requests.returns.update', $entry), ['decision' => 'approved'])->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('admin.requests.returns.update', $entry), ['decision' => 'approved', 'admin_note' => 'Bring the unused item.'])->assertRedirect();
        $this->assertDatabaseHas('return_exchange_requests', ['id' => $entry->id, 'status' => 'approved', 'reviewed_by' => $admin->id]);
    }

    public function test_guest_and_user_can_submit_questions_and_admin_can_resolve_them(): void
    {
        $payload = ['name' => 'Guest', 'email' => 'guest@example.com', 'question' => 'Which racket suits a beginner?'];
        $this->post(route('questions.store'), $payload)->assertRedirect();
        $this->actingAs(User::factory()->create())->post(route('questions.store'), ['name' => 'User', 'email' => 'user@example.com', 'question' => 'Is stringing available?'])->assertRedirect();
        $this->post(route('questions.store'), array_merge($payload, ['email' => 'invalid']))->assertSessionHasErrors('email');

        $question = CustomerQuestion::firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.requests.index'))->assertOk()->assertSee($question->question);
        $this->actingAs($admin)->patch(route('admin.requests.questions.update', $question), ['status' => 'resolved', 'admin_response' => 'We recommend a forgiving 100 sq in frame.'])->assertRedirect();
        $this->assertSame('resolved', $question->fresh()->status);
    }

    private function order(User $user, string $status): Order
    {
        return $user->orders()->create(['order_number' => 'ORD-'.str()->random(8), 'status' => $status, 'subtotal' => 100, 'total' => 100, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo']);
    }

    private function product(): Product
    {
        $category = Category::firstOrCreate(['slug' => 'tennis-rackets'], ['name' => 'Tennis Rackets']);

        return Product::create(['category_id' => $category->id, 'name' => 'Test Racket '.str()->random(4), 'slug' => 'test-racket-'.str()->random(8), 'price' => 100, 'stock' => 3, 'is_active' => true]);
    }
}
