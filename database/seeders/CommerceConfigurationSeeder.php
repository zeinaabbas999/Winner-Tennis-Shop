<?php

namespace Database\Seeders;

use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class CommerceConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            'Cairo' => 75, 'Giza' => 75, 'Alexandria' => 90, 'Qalyubia' => 85,
            'Dakahlia' => 100, 'Sharqia' => 100, 'Gharbia' => 100, 'Monufia' => 100,
            'Beheira' => 110, 'Kafr El Sheikh' => 110, 'Damietta' => 110, 'Port Said' => 110,
            'Ismailia' => 110, 'Suez' => 110, 'Fayoum' => 115, 'Beni Suef' => 115,
            'Minya' => 125, 'Assiut' => 125, 'Sohag' => 130, 'Qena' => 135,
            'Luxor' => 140, 'Aswan' => 150, 'Red Sea' => 150, 'New Valley' => 165,
            'Matrouh' => 150, 'North Sinai' => 165, 'South Sinai' => 165,
        ];
        foreach ($rates as $governorate => $fee) {
            ShippingRate::firstOrCreate(['governorate' => $governorate], ['fee' => $fee, 'free_shipping_threshold' => 5000, 'is_active' => true]);
        }
    }
}
