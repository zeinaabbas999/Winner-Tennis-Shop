<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('new_badge_mode', 20)->default('auto')->after('is_featured');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('loyalty_points')->default(0)->after('role');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('points_redeemed')->default(0)->after('coupon_discount');
            $table->decimal('points_discount', 10, 2)->default(0)->after('points_redeemed');
            $table->unsignedInteger('points_earned')->default(0)->after('points_discount');
            $table->timestamp('points_earned_at')->nullable()->after('points_earned');
            $table->timestamp('points_reversed_at')->nullable()->after('points_earned_at');
            $table->timestamp('points_redemption_restored_at')->nullable()->after('points_reversed_at');
        });

        Schema::create('policy_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('eyebrow')->nullable();
            $table->text('intro')->nullable();
            $table->json('sections');
            $table->date('effective_date')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40);
            $table->integer('points_change');
            $table->unsignedInteger('balance_after');
            $table->string('description', 500);
            $table->timestamps();
            $table->unique(['order_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });

        $today = now();
        $pages = [
            'privacy' => ['Privacy Policy', 'Customer care', 'How WINNER handles information used to provide our tennis store and fulfil orders.', [['Introduction', 'This policy explains how WINNER handles personal information when you browse, create an account, place an order, publish a review or contact us.'], ['Information we collect', 'We may collect account, contact, order, delivery, review, preference and limited technical information required to operate the store safely.'], ['How we use information', 'We use information to operate accounts and carts, fulfil orders, provide support, prevent abuse and maintain reliable services.'], ['Data retention and security', 'Records are retained as reasonably required for fulfilment, support, security and record-keeping. We use access controls, validation, signed links and secure sessions, but no internet service can guarantee absolute security.'], ['Your choices', 'You may update your account and contact WINNER about access, correction or deletion requests. Some historical records may need to be retained.'], ['Contact', 'Privacy questions can be sent to winner.tennis999@gmail.com.']]],
            'terms' => ['Terms and Conditions', 'Store terms', 'Practical terms for using WINNER and placing an order.', [['Using WINNER', 'Provide accurate information and protect your account credentials. Do not interfere with security or attempt unauthorized access.'], ['Products and availability', 'We aim to describe products accurately. Products and variants remain subject to availability, and cart placement does not reserve stock.'], ['Prices and orders', 'Prices are shown in EGP. Discounts, coupons, shipping and rewards are validated by WINNER when an order is placed. An order confirmation acknowledges receipt and does not represent completed payment.'], ['Delivery and cancellation', 'Delivery estimates can be affected by destination and circumstances outside reasonable control. Eligible cancellation requests require review.'], ['Returns and exchanges', 'Eligibility follows the published policies. Used, damaged, hygiene-sensitive, customized or strung items may be excluded unless faulty.'], ['Contact', 'Questions can be sent to winner.tennis999@gmail.com.']]],
            'cookies' => ['Cookie Policy', 'Your browser', 'How necessary cookies and preferences work on WINNER.', [['What cookies are', 'Cookies are small values stored by a browser so a website can remember a secure session, cart or preference between requests.'], ['Strictly necessary cookies', 'WINNER uses necessary session, authentication and CSRF-security cookies for requested functions.'], ['Preferences', 'Your cookie choice is stored in your browser. Rejecting optional cookies does not block necessary functionality.'], ['Optional cookies', 'WINNER does not currently activate analytics or advertising cookies.'], ['Browser controls', 'Browsers let you inspect, block or delete cookies, although doing so may sign you out or empty a session cart.'], ['Contact', 'Cookie questions can be sent to winner.tennis999@gmail.com.']]],
            'shipping' => ['Shipping Policy', 'Delivery', 'How WINNER prepares and delivers orders.', [['Order processing', 'Orders are prepared within a reasonable processing period after their details are confirmed.'], ['Delivery estimates', 'Timing depends on destination, courier availability, weather and public holidays. Estimates are not guarantees.'], ['Updates', 'WINNER provides order updates when applicable and may contact you if delivery details need clarification.']]],
            'returns' => ['Returns Policy', 'Customer care', 'Eligibility for returning unused products.', [['Eligibility', 'Unused products in original condition may be eligible for return within 14 days of delivery with proof of purchase.'], ['Exclusions', 'Used, damaged, opened hygiene-sensitive, customized or strung items may not qualify unless faulty.'], ['Requesting a return', 'Contact WINNER with your order number and item details so eligibility and the next steps can be reviewed.']]],
            'exchanges' => ['Exchanges Policy', 'Customer care', 'Options for eligible product exchanges.', [['Availability', 'Exchanges depend on stock availability and the condition of the returned item.'], ['Price differences', 'Any difference in price between the original and replacement product will be confirmed before an exchange is completed.'], ['Requesting an exchange', 'Contact WINNER with your order number and preferred replacement so available options can be reviewed.']]],
        ];

        foreach ($pages as $key => [$title, $eyebrow, $intro, $sections]) {
            DB::table('policy_pages')->insert([
                'key' => $key,
                'title' => $title,
                'eyebrow' => $eyebrow,
                'intro' => $intro,
                'sections' => json_encode(array_map(fn ($section) => ['heading' => $section[0], 'content' => $section[1]], $sections)),
                'effective_date' => $today->toDateString(),
                'is_visible' => true,
                'created_at' => $today,
                'updated_at' => $today,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('policy_pages');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['points_redeemed', 'points_discount', 'points_earned', 'points_earned_at', 'points_reversed_at', 'points_redemption_restored_at']);
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('loyalty_points'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('new_badge_mode'));
    }
};
