<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FirstOrderOffer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EngagementRefinementTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_order_offer_is_server_created_once_and_ineligible_after_an_order(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('checkout.first-order-offer'))->assertOk()->assertJsonStructure(['label']);
        $this->actingAs($user)->postJson(route('checkout.first-order-offer'))->assertOk();
        $this->assertDatabaseCount('first_order_offers', 1);

        $other = User::factory()->create();
        $this->order($other);
        $this->actingAs($other)->postJson(route('checkout.first-order-offer'))->assertUnprocessable();
    }

    public function test_coupon_and_first_order_offer_cannot_be_claimed_together(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['coupon_code' => 'COURT10'])->postJson(route('checkout.first-order-offer'))->assertUnprocessable();
        $this->assertDatabaseMissing('first_order_offers', ['user_id' => $user->id]);
    }

    public function test_claimed_offer_is_snapshotted_and_used_during_checkout(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Rackets', 'slug' => 'rackets']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Offer Racket', 'slug' => 'offer-racket', 'price' => 1000, 'stock' => 2, 'is_active' => true]);
        FirstOrderOffer::create(['user_id' => $user->id, 'reward_type' => 'fixed', 'reward_value' => 100, 'claimed_at' => now()]);
        $token = '18362427-4f5b-46ac-b81c-fce80da63cef';
        $payload = ['checkout_token' => $token, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '01000000000', 'governorate' => 'Cairo', 'city_area' => 'Nasr City', 'street' => 'Court Street', 'building_number' => '10'];

        $this->actingAs($user)->withSession(['cart' => [(string) $product->id => 1], 'checkout_tokens' => [$token]])->post(route('checkout.store'), $payload)->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('100 EGP off', $order->first_order_offer_label);
        $this->assertSame('100.00', $order->first_order_discount);
        $this->assertNotNull($user->firstOrderOffer->fresh()->used_at);
    }

    public function test_policy_request_cannot_reference_another_users_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->order($owner, ['status' => 'delivered']);
        $category = Category::create(['name' => 'Rackets', 'slug' => 'rackets']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Policy Racket', 'slug' => 'policy-racket', 'price' => 100, 'stock' => 1, 'is_active' => true]);
        $item = $order->items()->create(['product_id' => $product->id, 'product_name' => 'Racket', 'price' => 100, 'quantity' => 1, 'subtotal' => 100]);

        $this->actingAs($intruder)->post(route('policy-requests.store', 'return'), ['order_item_id' => $item->id, 'reason' => 'Request'])->assertForbidden();
    }

    private function order(User $user, array $attributes = []): Order
    {
        return $user->orders()->create(array_merge(['order_number' => 'ORD-'.str()->random(8), 'status' => 'pending', 'subtotal' => 100, 'total' => 100, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo'], $attributes));
    }
}
