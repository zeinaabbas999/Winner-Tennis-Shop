<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $otherId = DB::table('categories')->where('slug', 'other-tennis-accessories')->value('id');
            if (! $otherId) {
                $otherId = DB::table('categories')->insertGetId(['name' => 'Other Tennis Accessories', 'slug' => 'other-tennis-accessories', 'description' => 'Practical accessories for training and match day.', 'created_at' => now(), 'updated_at' => now()]);
            }
            $accessoryIds = DB::table('categories')->whereIn('slug', ['headbands', 'water-bottles', 'wristbands', 'racket-accessories', 'apparel', 'towels'])->pluck('id');
            DB::table('products')->whereIn('category_id', $accessoryIds)->update(['category_id' => $otherId]);
            $retiredIds = DB::table('categories')->whereIn('slug', ['tennis-shoes', 'training-equipment'])->pluck('id');
            DB::table('products')->whereIn('category_id', $retiredIds)->update(['category_id' => $otherId, 'is_active' => false]);
            DB::table('categories')->whereIn('slug', ['headbands', 'water-bottles', 'wristbands', 'racket-accessories', 'apparel', 'towels', 'tennis-shoes', 'training-equipment'])->delete();
        });
    }

    public function down(): void
    {
        // This safe data consolidation intentionally has no automatic reversal.
    }
};
