<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $updates = [
            'wilson-super-tour-9-pack-bag' => ['image' => 'products/bags/wilson-super-tour-9-pack.png', 'description' => 'A refined nine-racket bag with practical storage for match days and regular training.'],
            'babolat-rh6-pure-aero-bag' => ['image' => 'products/bags/babolat-rh6-pure-aero.png', 'description' => 'A streamlined six-racket bag with organized space for court essentials.'],
            'wilson-us-open-extra-duty-3-ball-can' => ['image' => 'products/balls/wilson-us-open.png', 'description' => 'Regular-duty US Open tennis balls offering a consistent response for training and match play.'],
            'dunlop-fort-all-court-4-ball-can' => ['name' => 'Australian Open Official 4 Ball Can', 'slug' => 'dunlop-australian-open-official-4-ball-can', 'image' => 'products/balls/dunlop-australian-open.png', 'description' => 'Dunlop Australian Open balls engineered for a consistent flight and dependable match feel.'],
            'wilson-pro-overgrip-3-pack' => ['image' => 'products/overgrips/wilson-pro-overgrip.png', 'description' => 'A comfortable three-pack overgrip with a clean, secure feel in the hand.'],
            'yonex-super-grap-3-pack' => ['image' => 'products/overgrips/yonex-super-grap.png', 'description' => 'A versatile overgrip offering tack, comfort and reliable moisture absorption.'],
            'luxilon-alu-power-125-set' => ['brand' => 'Wilson', 'name' => 'Revolve Spin 17 Reel', 'slug' => 'wilson-revolve-spin-17-reel', 'image' => 'products/strings/wilson-revolve-spin.png', 'price' => 2799, 'description' => 'A shaped polyester reel designed for players seeking spin, control and durable response.'],
            'solinco-hyper-g-16l-set' => ['brand' => 'Dunlop', 'name' => 'Explosive Tour 16 Reel', 'slug' => 'dunlop-explosive-tour-16-reel', 'image' => 'products/strings/dunlop-explosive-tour.png', 'price' => 2599, 'description' => 'A comfortable polyester reel balancing control, feel and dependable durability.'],
        ];

        foreach ($updates as $currentSlug => $attributes) {
            DB::table('products')->where('slug', $currentSlug)->update($attributes + ['updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Product records are intentionally retained to protect future order history.
    }
};
