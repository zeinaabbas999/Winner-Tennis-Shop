<?php

namespace Tests\Feature;

use App\Models\ApplicationSetting;
use App\Models\Order;
use App\Models\User;
use App\Support\VerificationRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUsersSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_update_verification_requirement(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($customer)->patch(route('admin.settings.update'), [
            'require_customer_email_verification' => false,
        ])->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'require_customer_email_verification' => false,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('application_settings', [
            'key' => VerificationRequirement::KEY,
            'value' => '0',
        ]);
    }

    public function test_disabling_verification_skips_email_and_allows_unverified_customer_access(): void
    {
        ApplicationSetting::where('key', VerificationRequirement::KEY)->update(['value' => '0']);
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'Unverified Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $response->assertRedirect(route('orders.index'));
        $this->assertFalse($customer->hasVerifiedEmail());
        Notification::assertNothingSent();
        $this->actingAs($customer)->get(route('orders.index'))->assertOk();
        $this->actingAs($customer)->get(route('checkout.create'))->assertRedirect(route('cart.index'));
    }

    public function test_re_enabling_verification_blocks_an_existing_unverified_customer(): void
    {
        $customer = User::factory()->unverified()->create();
        ApplicationSetting::where('key', VerificationRequirement::KEY)->update(['value' => '0']);
        $this->actingAs($customer)->get(route('orders.index'))->assertOk();

        ApplicationSetting::where('key', VerificationRequirement::KEY)->update(['value' => '1']);
        $this->actingAs($customer)->get(route('orders.index'))->assertRedirect(route('verification.notice'));
        $this->assertNull($customer->fresh()->email_verified_at);
    }

    public function test_admin_can_search_and_view_customers_with_order_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['name' => 'Court Customer']);
        $this->orderFor($customer, 750);

        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Court']))
            ->assertOk()
            ->assertSee('Court Customer')
            ->assertSee('750.00 EGP');

        $this->actingAs($admin)->get(route('admin.users.show', $customer->id))
            ->assertOk()
            ->assertSee('ORD-USER-TEST')
            ->assertSee(route('admin.orders.show', Order::first()));
    }

    public function test_archiving_customer_blocks_login_and_preserves_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['email' => 'archive@example.com']);
        $order = $this->orderFor($customer, 500);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $customer->id))->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $customer->id]);
        auth()->logout();
        $this->post(route('login'), ['email' => 'archive@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_account_cannot_be_archived_through_customer_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->id))->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    private function orderFor(User $customer, float $total): Order
    {
        return $customer->orders()->create([
            'order_number' => 'ORD-USER-TEST',
            'status' => 'completed',
            'subtotal' => $total,
            'total' => $total,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'phone' => '01000000000',
            'address' => 'Cairo',
        ]);
    }
}
