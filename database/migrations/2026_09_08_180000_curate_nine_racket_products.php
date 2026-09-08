<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('categories')->where('slug', 'tennis-rackets')->value('id');

        if (! $categoryId) {
            return;
        }

        $now = now();
        $rackets = [
            ['Wilson', 'Blade 100 V9', 'wilson-blade-100-v9', 13999, 10, 'A versatile Blade with a composed response for players who build points with feel and precision.', ['head_size' => '100 sq in', 'weight' => '300 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Control'], 'products/rackets/wilson-blade-100-v9.png'],
            ['Wilson', 'Clash 100L V3', 'wilson-clash-100l-v3', 12499, 9, 'A light, arm-friendly frame with easy handling for developing strokes and quick preparation.', ['head_size' => '100 sq in', 'weight' => '280 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Beginner to intermediate', 'play_style' => 'Comfort and power'], 'products/rackets/wilson-clash-100l-v3.png'],
            ['Wilson', 'Shift 99 V1', 'wilson-shift-99-v1', 14499, 8, 'A fast modern frame designed for confident acceleration and spin-oriented baseline tennis.', ['head_size' => '99 sq in', 'weight' => '300 g unstrung', 'string_pattern' => '16 x 20', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Spin'], 'products/rackets/wilson-shift-99-v1.png'],
            ['Yonex', 'PERCEPT 100', 'yonex-percept-100', 15499, 7, 'A stable, flexible racket for players who value ball feedback and controlled placement.', ['head_size' => '100 sq in', 'weight' => '300 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Control and feel'], 'products/rackets/yonex-percept-100.png'],
            ['Yonex', 'EZONE 100', 'yonex-ezone-100', 14999, 12, 'A forgiving 100 square inch frame offering accessible power and a comfortable all-court response.', ['head_size' => '100 sq in', 'weight' => '300 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'All-court power'], 'products/rackets/yonex-ezone-100.png'],
            ['Yonex', 'VCORE 100', 'yonex-vcore-100', 14999, 10, 'A lively, aerodynamic racket for aggressive players looking to shape the ball with spin.', ['head_size' => '100 sq in', 'weight' => '300 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Spin'], 'products/rackets/yonex-vcore-100.png'],
            ['Babolat', 'Pure Drive Gen11', 'babolat-pure-drive-gen11', 13999, 12, 'A powerful, responsive all-court racket with dependable depth and a familiar 100 square inch head.', ['head_size' => '100 sq in', 'weight' => '300 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Power'], 'products/rackets/babolat-pure-drive-gen11.png'],
            ['Babolat', 'Pure Aero Rafa', 'babolat-pure-aero-rafa', 14299, 8, 'A bold spin-focused racket suited to fast swings and heavy topspin from the baseline.', ['head_size' => '100 sq in', 'weight' => '290 g unstrung', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate', 'play_style' => 'Spin and power'], 'products/rackets/babolat-pure-aero-rafa.png'],
            ['Babolat', 'Pure Aero 98', 'babolat-pure-aero-98', 14999, 7, 'A precise Pure Aero for advanced hitters seeking spin with a more controlled launch.', ['head_size' => '98 sq in', 'weight' => '305 g unstrung', 'string_pattern' => '16 x 20', 'skill_level' => 'Advanced', 'play_style' => 'Spin and control'], 'products/rackets/babolat-pure-aero-98.png'],
        ];

        foreach ($rackets as [$brand, $name, $slug, $price, $stock, $description, $specs, $image]) {
            DB::table('products')->updateOrInsert(
                ['slug' => $slug],
                [
                    'category_id' => $categoryId,
                    'brand' => $brand,
                    'name' => $name,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $description,
                    'image' => $image,
                    'specs' => json_encode($specs),
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        DB::table('products')
            ->where('category_id', $categoryId)
            ->whereNotIn('brand', ['Wilson', 'Yonex', 'Dunlop', 'Babolat'])
            ->update(['is_active' => false, 'updated_at' => $now]);
    }

    public function down(): void
    {
        // Deliberately retain catalogue records to protect products that may later appear in orders.
    }
};
