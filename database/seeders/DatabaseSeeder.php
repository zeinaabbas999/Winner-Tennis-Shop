<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $catalogue = [
            'Tennis Rackets' => [
                ['Wilson', 'Blade 100 V9', 13999, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Control'], 'products/rackets/wilson-blade-100-v9.png'],
                ['Wilson', 'Clash 100L V3', 12499, ['weight' => '280 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Beginner to intermediate', 'play_style' => 'Comfort and power'], 'products/rackets/wilson-clash-100l-v3.png'],
                ['Wilson', 'Shift 99 V1', 14499, ['weight' => '300 g unstrung', 'head_size' => '99 sq in', 'string_pattern' => '16 x 20', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Spin'], 'products/rackets/wilson-shift-99-v1.png'],
                ['Yonex', 'PERCEPT 100', 15499, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Control and feel'], 'products/rackets/yonex-percept-100.png'],
                ['Yonex', 'EZONE 100', 14999, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'All-court power'], 'products/rackets/yonex-ezone-100.png'],
                ['Yonex', 'VCORE 100', 14999, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Spin'], 'products/rackets/yonex-vcore-100.png'],
                ['Babolat', 'Pure Drive Gen11', 13999, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Power'], 'products/rackets/babolat-pure-drive-gen11.png'],
                ['Babolat', 'Pure Aero Rafa', 14299, ['weight' => '290 g unstrung', 'head_size' => '100 sq in', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate', 'play_style' => 'Spin and power'], 'products/rackets/babolat-pure-aero-rafa.png'],
                ['Babolat', 'Pure Aero 98', 14999, ['weight' => '305 g unstrung', 'head_size' => '98 sq in', 'string_pattern' => '16 x 20', 'skill_level' => 'Advanced', 'play_style' => 'Spin and control'], 'products/rackets/babolat-pure-aero-98.png'],
            ],
            'Tennis Bags' => [['Wilson', 'Super Tour 9 Pack Bag', 5499, [], 'products/bags/wilson-super-tour-9-pack.png'], ['Babolat', 'RH6 Pure Aero Bag', 4999, [], 'products/bags/babolat-rh6-pure-aero.png']],
            'Tennis Balls' => [['Wilson', 'US Open Extra Duty 3 Ball Can', 399, [], 'products/balls/wilson-us-open.png'], ['Dunlop', 'Australian Open Official 4 Ball Can', 449, [], 'products/balls/dunlop-australian-open.png']],
            'Grips and Overgrips' => [['Wilson', 'Pro Overgrip 3 Pack', 499, [], 'products/overgrips/wilson-pro-overgrip.png'], ['Yonex', 'Super Grap 3 Pack', 525, [], 'products/overgrips/yonex-super-grap.png']],
            'Strings' => [['Wilson', 'Revolve Spin 17 Reel', 2799, [], 'products/strings/wilson-revolve-spin.png'], ['Dunlop', 'Explosive Tour 16 Reel', 2599, [], 'products/strings/dunlop-explosive-tour.png']],
            'Dampeners' => [['Babolat', 'Custom Damp 2 Pack', 349, [], 'products/accessories/dampeners.png']], 'Wristbands' => [['Adidas', 'Performance Tennis Wristbands', 399, [], 'products/accessories/tennis-wristbands.png']],
            'Headbands' => [['The North Face', 'Court Terry Headband', 449, [], 'products/accessories/court-headband.png']], 'Towels' => [['Iron Roots', 'Performance Court Towel', 599, [], 'products/accessories/court-towel.png']],
            'Water Bottles' => [['Playe Pose', 'Insulated Court Bottle', 749, [], 'products/accessories/court-bottle.png']], 'Racket Accessories' => [['Babolat', 'Racket Protection Tape', 299, []]],
            'Training Equipment' => [['HEAD', 'Target Cone Set', 799, []]], 'Tennis Shoes' => [['Babolat', 'Jet Mach 3 All Court', 6499, []]],
            'Apparel' => [['WINNER', 'Club Performance Polo', 1299, []]], 'Other Tennis Accessories' => [['WINNER', 'Court Essentials Kit', 999, []]],
        ];

        foreach ($catalogue as $categoryName => $products) {
            $category = Category::firstOrCreate(['slug' => str($categoryName)->slug()], ['name' => $categoryName, 'description' => "Shop {$categoryName} selected for club and competitive play."]);
            foreach ($products as $product) {
                [$brand, $name, $price, $specs] = $product;
                Product::firstOrCreate(['slug' => str($brand.' '.$name)->slug()], ['category_id' => $category->id, 'brand' => $brand, 'name' => $name, 'description' => "{$brand} {$name} is selected for dependable performance on court. Specifications are shown where they materially affect racket selection.", 'specs' => $specs ?: null, 'price' => $price, 'stock' => 12, 'image' => $product[4] ?? null, 'is_active' => true]);
            }
        }

        $otherId = Category::where('slug', 'other-tennis-accessories')->value('id');
        $accessoryIds = Category::whereIn('slug', ['headbands', 'water-bottles', 'wristbands', 'racket-accessories', 'apparel', 'towels'])->pluck('id');
        Product::whereIn('category_id', $accessoryIds)->update(['category_id' => $otherId]);
        $retiredIds = Category::whereIn('slug', ['tennis-shoes', 'training-equipment'])->pluck('id');
        Product::whereIn('category_id', $retiredIds)->update(['category_id' => $otherId, 'is_active' => false]);
        Category::whereIn('slug', ['headbands', 'water-bottles', 'wristbands', 'racket-accessories', 'apparel', 'towels', 'tennis-shoes', 'training-equipment'])->delete();
    }
}
