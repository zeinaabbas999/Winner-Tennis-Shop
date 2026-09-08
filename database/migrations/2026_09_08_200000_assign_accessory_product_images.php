<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $updates = [
            'babolat-custom-damp-2-pack' => ['image' => 'products/accessories/dampeners.png', 'description' => 'A colorful two-pack of vibration dampeners for a softer, quieter feel at impact.'],
            'wilson-performance-wristband' => ['brand' => 'Adidas', 'name' => 'Performance Tennis Wristbands', 'slug' => 'adidas-performance-tennis-wristbands', 'image' => 'products/accessories/tennis-wristbands.png', 'description' => 'Absorbent tennis wristbands designed for comfort through warm training sessions.'],
            'head-pro-player-headband' => ['brand' => 'The North Face', 'name' => 'Court Terry Headband', 'slug' => 'the-north-face-court-terry-headband', 'image' => 'products/accessories/court-headband.png', 'description' => 'A soft terry headband that keeps sweat controlled without feeling bulky.'],
            'winner-court-towel' => ['brand' => 'Iron Roots', 'name' => 'Performance Court Towel', 'slug' => 'iron-roots-performance-court-towel', 'image' => 'products/accessories/court-towel.png', 'description' => 'A clean, absorbent court towel sized for training bags and match changeovers.'],
            'winner-insulated-court-bottle' => ['brand' => 'Playe Pose', 'name' => 'Insulated Court Bottle', 'slug' => 'playe-pose-insulated-court-bottle', 'image' => 'products/accessories/court-bottle.png', 'description' => 'An insulated metal bottle built to keep water cool from warm-up to the final set.'],
        ];

        DB::transaction(function () use ($updates, $now): void {
            foreach ($updates as $currentSlug => $attributes) {
                DB::table('products')->where('slug', $currentSlug)->update($attributes + ['updated_at' => $now]);
            }
        });
    }

    public function down(): void
    {
        // Records are retained to protect any order history created after deployment.
    }
};
