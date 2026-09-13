<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('first_order_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('reward_type', 24);
            $table->decimal('reward_value', 10, 2)->default(0);
            $table->timestamp('claimed_at');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('first_order_offer_label')->nullable()->after('coupon_discount');
            $table->decimal('first_order_discount', 10, 2)->default(0)->after('first_order_offer_label');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['first_order_offer_label', 'first_order_discount']);
        });
        Schema::dropIfExists('first_order_offers');
    }
};
