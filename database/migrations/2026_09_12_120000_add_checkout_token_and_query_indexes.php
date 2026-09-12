<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_token')->nullable()->unique()->after('order_number');
            $table->index(['status', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'category_id', 'brand']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'category_id', 'brand']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropUnique(['checkout_token']);
            $table->dropColumn('checkout_token');
        });
    }
};
