<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpandedProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'tennis-rackets',
            'tennis-balls',
            'tennis-bags',
            'strings',
            'grips-and-overgrips',
            'dampeners',
            'other-tennis-accessories',
        ])->mapWithKeys(function (string $slug): array {
            $category = Category::where('slug', $slug)->firstOrFail();

            return [$slug => $category->id];
        });

        $products = [
            // Rackets
            $this->racket('Wilson', 'Blade 98 16x19 V9', 'wilson-blade-98-16x19-v9', 15750, 305, 98, '16 x 19', 'Advanced', 'Control and feel'),
            $this->racket('Wilson', 'Clash 100 V3', 'wilson-clash-100-v3', 15250, 293, 100, '16 x 19', 'Intermediate', 'Comfort and control'),
            $this->racket('Wilson', 'Pro Staff 97 V14', 'wilson-pro-staff-97-v14', 16250, 315, 97, '16 x 19', 'Advanced', 'Precision and feel'),
            $this->racket('Wilson', 'Ultra 100 V4', 'wilson-ultra-100-v4', 13950, 300, 100, '16 x 19', 'Intermediate', 'Easy power'),
            $this->racket('Yonex', 'EZONE 98', 'yonex-ezone-98', 15950, 305, 98, '16 x 19', 'Intermediate to advanced', 'Controllable power'),
            $this->racket('Yonex', 'VCORE 98', 'yonex-vcore-98', 15950, 305, 98, '16 x 19', 'Advanced', 'Spin and precision'),
            $this->racket('Yonex', 'PERCEPT 97', 'yonex-percept-97', 16450, 310, 97, '16 x 19', 'Advanced', 'Control and feel'),
            $this->racket('Babolat', 'Pure Strike 100 Gen4', 'babolat-pure-strike-100-gen4', 15450, 300, 100, '16 x 19', 'Intermediate to advanced', 'Control and feel'),
            $this->racket('Babolat', 'Pure Drive 98 Gen11', 'babolat-pure-drive-98-gen11', 16250, 305, 98, '16 x 20', 'Advanced', 'Power and precision'),
            $this->racket('Dunlop', 'FX 500', 'dunlop-fx-500', 13950, 300, 100, '16 x 19', 'Intermediate', 'Power and stability'),
            $this->racket('Dunlop', 'CX 200', 'dunlop-cx-200', 14450, 305, 98, '16 x 19', 'Advanced', 'Control and touch'),
            $this->racket('Dunlop', 'SX 300', 'dunlop-sx-300', 14250, 300, 100, '16 x 19', 'Intermediate to advanced', 'Spin and power'),

            // Balls
            $this->product('tennis-balls', 'Wilson', 'Championship Extra Duty 3 Ball Can', 'wilson-championship-extra-duty-3-ball-can', 325, 'A durable pressurized ball for hard-court practice, club matches and regular training.'),
            $this->product('tennis-balls', 'Wilson', 'Triniti Pro 4 Ball Can', 'wilson-triniti-pro-4-ball-can', 575, 'A premium performance ball designed for consistent play with more sustainable packaging.'),
            $this->product('tennis-balls', 'Yonex', 'Tour Tennis Balls 4 Ball Can', 'yonex-tour-tennis-balls-4-ball-can', 475, 'Tournament-grade pressurized balls with a consistent bounce and durable woven felt.'),
            $this->product('tennis-balls', 'Babolat', 'Gold Championship 4 Ball Can', 'babolat-gold-championship-4-ball-can', 425, 'A lively all-court ball suited to training sessions and competitive club play.'),
            $this->product('tennis-balls', 'Dunlop', 'ATP Championship 4 Ball Can', 'dunlop-atp-championship-4-ball-can', 450, 'A versatile ball with dependable feel and durability for practice and match play.'),
            $this->product('tennis-balls', 'Dunlop', 'Fort All Court 4 Ball Can', 'dunlop-fort-all-court-4-ball-can', 525, 'A premium all-court tennis ball made for consistent flight, bounce and lasting playability.'),

            // Bags
            $this->product('tennis-bags', 'Wilson', 'Blade Super Tour 9 Pack Bag', 'wilson-blade-super-tour-9-pack-bag', 6450, 'A structured nine-racket performance bag with organized equipment and accessory storage.'),
            $this->product('tennis-bags', 'Wilson', 'Clash V3 Super Tour 6 Pack Bag', 'wilson-clash-v3-super-tour-6-pack-bag', 5650, 'A six-racket tour bag balancing court-ready capacity with a compact carry profile.'),
            $this->product('tennis-bags', 'Yonex', 'Pro Racquet Bag 9 Pack', 'yonex-pro-racquet-bag-9-pack', 6250, 'A tournament bag with generous racket capacity and separated storage for court essentials.'),
            $this->product('tennis-bags', 'Yonex', 'Pro Racquet Bag 6 Pack', 'yonex-pro-racquet-bag-6-pack', 5450, 'A streamlined six-racket bag for players carrying a complete match and training setup.'),
            $this->product('tennis-bags', 'Babolat', 'RH12 Pure Aero Bag', 'babolat-rh12-pure-aero-bag', 7250, 'A high-capacity twelve-racket bag with dedicated compartments for competitive players.'),
            $this->product('tennis-bags', 'Babolat', 'Pure Drive RH6 Bag', 'babolat-pure-drive-rh6-bag', 5750, 'A practical six-racket bag with organized space for footwear, accessories and personal items.'),
            $this->product('tennis-bags', 'Dunlop', 'SX Performance 12 Racket Bag', 'dunlop-sx-performance-12-racket-bag', 6650, 'A spacious performance bag built to carry up to twelve rackets and tournament equipment.'),
            $this->product('tennis-bags', 'Dunlop', 'FX Performance 8 Racket Bag', 'dunlop-fx-performance-8-racket-bag', 5950, 'An eight-racket performance bag with balanced capacity for regular competition and travel.'),

            // Strings
            $this->product('strings', 'Wilson', 'NXT 16 String Set', 'wilson-nxt-16-string-set', 950, 'A premium multifilament string set offering arm-friendly comfort, feel and accessible power.'),
            $this->product('strings', 'Wilson', 'Sensation 16 String Set', 'wilson-sensation-16-string-set', 625, 'A comfortable multifilament string with a responsive feel for recreational and club players.'),
            $this->product('strings', 'Wilson', 'Revolve 16 String Set', 'wilson-revolve-16-string-set', 725, 'A durable co-polyester set designed to support spin, control and aggressive baseline play.'),
            $this->product('strings', 'Yonex', 'PolyTour Pro 125 String Set', 'yonex-polytour-pro-125-string-set', 825, 'A smooth polyester string known for controlled response and a comfortable, predictable feel.'),
            $this->product('strings', 'Yonex', 'PolyTour Rev 125 String Set', 'yonex-polytour-rev-125-string-set', 875, 'A shaped polyester string engineered for bite, snapback and heavy spin production.'),
            $this->product('strings', 'Yonex', 'Rexis Speed 130 String Set', 'yonex-rexis-speed-130-string-set', 900, 'A responsive multifilament string for players seeking comfort, speed and easy power.'),
            $this->product('strings', 'Babolat', 'RPM Blast 125 String Set', 'babolat-rpm-blast-125-string-set', 875, 'A co-polyester string set made for spin, durability and control on full, fast swings.'),
            $this->product('strings', 'Babolat', 'RPM Team 125 String Set', 'babolat-rpm-team-125-string-set', 825, 'A spin-focused polyester string with a slightly softer response than firmer competition strings.'),
            $this->product('strings', 'Babolat', 'Xcel 130 String Set', 'babolat-xcel-130-string-set', 925, 'A multifilament string set providing comfort, power and a forgiving response at impact.'),
            $this->product('strings', 'Dunlop', 'Explosive Spin 16 String Set', 'dunlop-explosive-spin-16-string-set', 750, 'A shaped polyester string developed for controlled power, string movement and spin.'),

            // Grips and overgrips
            $this->product('grips-and-overgrips', 'Wilson', 'Pro Overgrip Sensation 3 Pack', 'wilson-pro-overgrip-sensation-3-pack', 525, 'A thin, tacky overgrip for players who prefer direct handle feel and dependable absorption.'),
            $this->product('grips-and-overgrips', 'Wilson', 'AbsorbX Overgrip 3 Pack', 'wilson-absorbx-overgrip-3-pack', 550, 'A dry-feel overgrip designed for strong moisture absorption and durable comfort.'),
            $this->product('grips-and-overgrips', 'Yonex', 'Dry Super Grap 3 Pack', 'yonex-dry-super-grap-3-pack', 575, 'A dry-touch overgrip offering secure control for humid conditions and high-intensity play.'),
            $this->product('grips-and-overgrips', 'Babolat', 'VS Original Overgrip 3 Pack', 'babolat-vs-original-overgrip-3-pack', 525, 'A slim performance overgrip with a dry, direct feel and minimal handle buildup.'),
            $this->product('grips-and-overgrips', 'Babolat', 'Pro Tour Overgrip 3 Pack', 'babolat-pro-tour-overgrip-3-pack', 500, 'A tacky overgrip balancing absorption, comfort and a secure hold during long sessions.'),
            $this->product('grips-and-overgrips', 'Dunlop', 'Super Tac Overgrip 3 Pack', 'dunlop-super-tac-overgrip-3-pack', 475, 'A tacky overgrip that maintains secure handle contact through training and match play.'),

            // Dampeners
            $this->product('dampeners', 'Wilson', 'Pro Feel Dampener 2 Pack', 'wilson-pro-feel-dampener-2-pack', 350, 'A compact pair of dampeners that reduces string-bed vibration without changing racket balance.'),
            $this->product('dampeners', 'Yonex', 'Vibration Dampener 2 Pack', 'yonex-vibration-dampener-2-pack', 350, 'A lightweight dampener set for a cleaner impact sound and reduced string vibration.'),
            $this->product('dampeners', 'Babolat', 'Aero Damp 2 Pack', 'babolat-aero-damp-2-pack', 375, 'A pair of compact dampeners designed to soften string vibration and impact sound.'),
            $this->product('dampeners', 'Dunlop', 'Tour Pro Vibration Dampener 2 Pack', 'dunlop-tour-pro-vibration-dampener-2-pack', 325, 'A practical two-pack that provides a quieter, more controlled impact sensation.'),

            // Other accessories and replacement grips
            $this->product('other-tennis-accessories', 'Wilson', 'Premium Leather Replacement Grip', 'wilson-premium-leather-replacement-grip', 950, 'A firm leather replacement grip for defined bevel feel and a classic connected response.'),
            $this->product('other-tennis-accessories', 'Yonex', 'Synthetic Leather Excel Pro Grip', 'yonex-synthetic-leather-excel-pro-grip', 650, 'A cushioned replacement grip providing secure traction and balanced handle comfort.'),
            $this->product('other-tennis-accessories', 'Babolat', 'Syntec Pro Replacement Grip', 'babolat-syntec-pro-replacement-grip', 675, 'A firm replacement grip offering a direct feel and dependable tack for regular play.'),
            $this->product('other-tennis-accessories', 'Dunlop', 'Gecko-Tac Replacement Grip', 'dunlop-gecko-tac-replacement-grip', 625, 'A tacky replacement grip engineered for controlled handling and lasting comfort.'),
        ];

        DB::transaction(function () use ($categories, $products): void {
            foreach ($products as $product) {
                $categorySlug = $product['category'];
                unset($product['category']);

                Product::firstOrCreate(
                    ['slug' => $product['slug']],
                    array_merge($product, [
                        'category_id' => $categories[$categorySlug],
                        'image' => null,
                        'discount_percent' => 0,
                        'stock' => 12,
                        'is_active' => true,
                    ]),
                );
            }
        });
    }

    private function racket(
        string $brand,
        string $name,
        string $slug,
        int $price,
        int $weight,
        int $headSize,
        string $stringPattern,
        string $skillLevel,
        string $playStyle,
    ): array {
        return $this->product(
            'tennis-rackets',
            $brand,
            $name,
            $slug,
            $price,
            "The {$name} is a performance frame selected for players seeking {$playStyle} with dependable court response.",
            [
                'weight' => "{$weight} g unstrung",
                'head_size' => "{$headSize} sq in",
                'string_pattern' => $stringPattern,
                'skill_level' => $skillLevel,
                'play_style' => $playStyle,
            ],
        );
    }

    private function product(
        string $category,
        string $brand,
        string $name,
        string $slug,
        int $price,
        string $description,
        ?array $specs = null,
    ): array {
        return compact('category', 'brand', 'name', 'slug', 'description', 'specs', 'price');
    }
}
