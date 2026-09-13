<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PolicyPage;
use App\Models\Product;
use App\Models\User;
use App\Support\Loyalty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoyaltyPolicyAndBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_policies_use_stored_content_and_only_admin_can_update_it(): void
    {
        $policy = PolicyPage::where('key', 'privacy')->firstOrFail();
        $this->get(route('privacy'))->assertOk()->assertSee($policy->title)->assertSee($policy->sections[0]['heading']);

        $payload = ['title' => 'Updated Privacy', 'eyebrow' => 'Care', 'intro' => 'Updated introduction.', 'effective_date' => now()->toDateString(), 'is_visible' => 1, 'sections' => [['heading' => 'Scope', 'content' => 'Safely escaped content.']]];
        $this->actingAs(User::factory()->create())->put(route('admin.policies.update', $policy), $payload)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.policies.update', $policy), $payload)->assertRedirect(route('admin.policies.index'));
        $this->get(route('privacy'))->assertSee('Updated Privacy')->assertSee('Safely escaped content.');
    }

    public function test_new_badge_uses_strict_seven_day_window_and_admin_overrides(): void
    {
        $category = Category::create(['name' => 'Rackets', 'slug' => 'rackets']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Test', 'slug' => 'test', 'price' => 100, 'stock' => 1, 'is_active' => true, 'new_badge_mode' => 'auto']);

        $product->forceFill(['created_at' => now()->subDays(6)])->save();
        $this->assertTrue($product->fresh()->showsNewBadge());
        $product->forceFill(['created_at' => now()->subDays(7)])->save();
        $this->assertFalse($product->fresh()->showsNewBadge());
        $product->update(['new_badge_mode' => 'force_on']);
        $this->assertTrue($product->fresh()->showsNewBadge());
        $product->update(['new_badge_mode' => 'force_off']);
        $this->assertFalse($product->fresh()->showsNewBadge());
    }

    public function test_loyalty_math_awards_once_excludes_shipping_and_accounts_for_discounts(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['subtotal' => 1000, 'coupon_discount' => 100, 'points_discount' => 50, 'shipping_fee' => 70, 'total' => 920]);
        $loyalty = app(Loyalty::class);

        \DB::transaction(fn () => $loyalty->awardForDelivered(Order::lockForUpdate()->findOrFail($order->id)));
        \DB::transaction(fn () => $loyalty->awardForDelivered(Order::lockForUpdate()->findOrFail($order->id)));

        $this->assertSame(85, $user->fresh()->loyalty_points);
        $this->assertSame(85, $order->fresh()->points_earned);
        $this->assertDatabaseCount('loyalty_transactions', 1);
        $this->assertSame('10.00', $loyalty->redemptionDiscount(100));

        $second = $this->order($user, ['subtotal' => 109, 'total' => 109]);
        \DB::transaction(fn () => $loyalty->awardForDelivered(Order::lockForUpdate()->findOrFail($second->id)));
        $this->assertSame(10, $second->fresh()->points_earned);
    }

    public function test_checkout_deducts_valid_points_and_snapshots_the_discount(): void
    {
        Mail::fake();
        $user = User::factory()->create(['loyalty_points' => 500]);
        $category = Category::create(['name' => 'Rackets', 'slug' => 'rackets']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Checkout Racket', 'slug' => 'checkout-racket', 'price' => 1000, 'stock' => 2, 'is_active' => true]);
        $token = '18362427-4f5b-46ac-b81c-fce80da63cef';
        $payload = ['checkout_token' => $token, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '01000000000', 'governorate' => 'Cairo', 'city_area' => 'Nasr City', 'street' => 'Court Street', 'building_number' => '10', 'points_to_redeem' => 200];

        $this->actingAs($user)->withSession(['cart' => [$product->id => 1], 'checkout_tokens' => [$token]])->post(route('checkout.store'), $payload)->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(200, $order->points_redeemed);
        $this->assertSame('20.00', $order->points_discount);
        $this->assertSame('980.00', $order->total);
        $this->assertSame(300, $user->fresh()->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', ['order_id' => $order->id, 'type' => 'redeemed', 'points_change' => -200]);
    }

    public function test_redemption_and_cancellation_restoration_are_atomic_and_once_only(): void
    {
        $user = User::factory()->create(['loyalty_points' => 500]);
        $order = $this->order($user, ['points_redeemed' => 200, 'points_discount' => 20]);
        $loyalty = app(Loyalty::class);

        \DB::transaction(fn () => $loyalty->redeem(User::lockForUpdate()->findOrFail($user->id), $order, 200, '100.00'));
        $this->assertSame(300, $user->fresh()->loyalty_points);
        \DB::transaction(fn () => $loyalty->reverseForCancellation(Order::lockForUpdate()->findOrFail($order->id)));
        \DB::transaction(fn () => $loyalty->reverseForCancellation(Order::lockForUpdate()->findOrFail($order->id)));
        $this->assertSame(500, $user->fresh()->loyalty_points);
        $this->assertSame(1, $user->loyaltyTransactions()->where('type', 'cancellation_restoration')->count());
    }

    public function test_non_admin_cannot_adjust_points_and_admin_adjustment_is_audited(): void
    {
        $customer = User::factory()->create();
        $this->actingAs(User::factory()->create())->post(route('admin.users.points.store', $customer), ['points_change' => 100, 'reason' => 'Not allowed'])->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.users.points.store', $customer), ['points_change' => 100, 'reason' => 'Service recovery'])->assertRedirect();
        $this->assertSame(100, $customer->fresh()->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', ['user_id' => $customer->id, 'admin_user_id' => $admin->id, 'type' => 'admin_adjustment', 'points_change' => 100]);
    }

    private function order(User $user, array $attributes = []): Order
    {
        return $user->orders()->create(array_merge(['order_number' => 'ORD-'.str()->random(8), 'status' => 'pending', 'subtotal' => 109, 'coupon_discount' => 0, 'points_discount' => 0, 'shipping_fee' => 0, 'total' => 109, 'customer_name' => $user->name, 'customer_email' => $user->email, 'phone' => '1', 'address' => 'Cairo'], $attributes));
    }
}
