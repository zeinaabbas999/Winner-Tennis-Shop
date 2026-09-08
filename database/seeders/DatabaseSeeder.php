<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $catalogue = [
            'Dresses' => ['Silk Column Dress', 'Draped Evening Dress'],
            'Jackets' => ['Sculpted Wool Blazer', 'Cropped Leather Jacket'],
            'Tops' => ['Fine Knit Top', 'Architectural Shirt'],
            'Bottoms' => ['Wide Leg Trouser', 'Bias Midi Skirt'],
            'Accessories' => ['Soft Leather Tote', 'Sculptural Cuff'],
            'Shoes' => ['Minimal Leather Mule', 'Square Toe Boot'],
        ];
        foreach ($catalogue as $name => $products) {
            $category = Category::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name, 'description' => "Explore our {$name} edit."]);
            foreach ($products as $index => $productName) {
                Product::firstOrCreate(['slug' => str($productName)->slug()], ['category_id' => $category->id, 'name' => $productName, 'description' => 'A refined wardrobe essential designed with an editorial point of view.', 'price' => 950 + ($index * 475), 'stock' => 12, 'is_active' => true]);
            }
        }
    }
}
