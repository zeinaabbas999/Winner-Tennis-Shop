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
                ['Yonex', 'EZONE 100', 14999, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'balance' => '320 mm', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'All court power']],
                ['Babolat', 'Pure Drive Gen11', 13999, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'balance' => '320 mm', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Power']],
                ['HEAD', 'Speed MP 2026', 15499, ['weight' => '300 g unstrung', 'head_size' => '100 sq in', 'balance' => '320 mm', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'All court']],
                ['Tecnifibre', 'T-Fight 300S', 14499, ['weight' => '300 g unstrung', 'head_size' => '98 sq in', 'balance' => '320 mm', 'string_pattern' => '16 x 19', 'skill_level' => 'Intermediate to advanced', 'play_style' => 'Control and maneuverability']],
            ],
            'Tennis Bags' => [['Wilson', 'Super Tour 9 Pack Bag', 5499, []], ['Babolat', 'RH6 Pure Aero Bag', 4999, []]],
            'Tennis Balls' => [['Wilson', 'US Open Extra Duty 3 Ball Can', 399, []], ['Dunlop', 'Fort All Court 4 Ball Can', 449, []]],
            'Grips and Overgrips' => [['Wilson', 'Pro Overgrip 3 Pack', 499, []], ['Yonex', 'Super Grap 3 Pack', 525, []]],
            'Strings' => [['Luxilon', 'ALU Power 125 Set', 1199, []], ['Solinco', 'Hyper-G 16L Set', 899, []]],
            'Dampeners' => [['Babolat', 'Custom Damp 2 Pack', 349, []]], 'Wristbands' => [['Wilson', 'Performance Wristband', 399, []]],
            'Headbands' => [['HEAD', 'Pro Player Headband', 449, []]], 'Towels' => [['WINNER', 'Court Towel', 599, []]],
            'Water Bottles' => [['WINNER', 'Insulated Court Bottle', 749, []]], 'Racket Accessories' => [['Babolat', 'Racket Protection Tape', 299, []]],
            'Training Equipment' => [['HEAD', 'Target Cone Set', 799, []]], 'Tennis Shoes' => [['Babolat', 'Jet Mach 3 All Court', 6499, []]],
            'Apparel' => [['WINNER', 'Club Performance Polo', 1299, []]], 'Other Tennis Accessories' => [['WINNER', 'Court Essentials Kit', 999, []]],
        ];

        foreach ($catalogue as $categoryName => $products) {
            $category = Category::firstOrCreate(['slug' => str($categoryName)->slug()], ['name' => $categoryName, 'description' => "Shop {$categoryName} selected for club and competitive play."]);
            foreach ($products as [$brand, $name, $price, $specs]) {
                Product::firstOrCreate(['slug' => str($brand.' '.$name)->slug()], ['category_id' => $category->id, 'brand' => $brand, 'name' => $name, 'description' => "{$brand} {$name} is selected for dependable performance on court. Specifications are shown where they materially affect racket selection.", 'specs' => $specs ?: null, 'price' => $price, 'stock' => 12, 'is_active' => true]);
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
