<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->index()->after('is_active');
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->json('attributes')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('quantity_change');
            $table->string('reason', 50);
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type', 20);
            $table->decimal('value', 10, 2);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_user_limit')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('governorate')->unique();
            $table->decimal('fee', 10, 2);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('guest_access_token', 64)->nullable()->unique()->after('checkout_token');
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('cancellation_status', 20)->nullable()->index();
            $table->timestamp('cancellation_reviewed_at')->nullable();
            $table->foreignId('cancellation_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('variant_name')->nullable()->after('product_name');
            $table->json('variant_attributes')->nullable()->after('variant_name');
            $table->string('variant_sku')->nullable()->after('variant_attributes');
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_email');
            $table->timestamps();
            $table->unique(['coupon_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn(['variant_name', 'variant_attributes', 'variant_sku']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancellation_reviewed_by');
            $table->dropUnique(['guest_access_token']);
            $table->dropColumn(['guest_access_token', 'coupon_code', 'coupon_discount', 'shipping_fee', 'cancellation_requested_at', 'cancellation_reason', 'cancellation_status', 'cancellation_reviewed_at']);
        });
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('wishlists');
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('is_featured'));
    }
};
