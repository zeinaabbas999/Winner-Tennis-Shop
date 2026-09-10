<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\ExpandedProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpandedProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_expanded_catalogue_seeder_is_idempotent_and_preserves_existing_products(): void
    {
        foreach ([
            'tennis-rackets' => 'Tennis Rackets',
            'tennis-balls' => 'Tennis Balls',
            'tennis-bags' => 'Tennis Bags',
            'strings' => 'Strings',
            'grips-and-overgrips' => 'Grips and Overgrips',
            'dampeners' => 'Dampeners',
            'other-tennis-accessories' => 'Other Tennis Accessories',
        ] as $slug => $name) {
            Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $rackets = Category::where('slug', 'tennis-rackets')->firstOrFail();
        Product::create([
            'category_id' => $rackets->id,
            'brand' => 'Wilson',
            'name' => 'Existing Blade Record',
            'slug' => 'wilson-blade-98-16x19-v9',
            'price' => 12345,
            'stock' => 2,
            'is_active' => true,
        ]);

        $this->seed(ExpandedProductSeeder::class);
        $this->seed(ExpandedProductSeeder::class);

        $this->assertDatabaseCount('products', 50);
        $this->assertDatabaseHas('products', [
            'slug' => 'wilson-blade-98-16x19-v9',
            'name' => 'Existing Blade Record',
            'price' => 12345,
        ]);
        $this->assertDatabaseHas('products', [
            'slug' => 'dunlop-sx-300',
            'brand' => 'Dunlop',
            'category_id' => $rackets->id,
        ]);
    }
}
