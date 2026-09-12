<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_load_and_cookie_controls_are_present(): void
    {
        foreach (['privacy', 'terms', 'cookies'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('home'))
            ->assertSee('Cookie preferences')
            ->assertSee('data-cookie-choice="necessary"', false)
            ->assertSee('data-cookie-settings', false);
    }

    public function test_public_responses_include_baseline_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');
    }

    public function test_sitemap_contains_public_catalogue_and_excludes_private_routes(): void
    {
        $category = Category::create(['name' => 'Rackets', 'slug' => 'rackets']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Public Racket',
            'slug' => 'public-racket',
            'price' => 1000,
            'stock' => 1,
            'is_active' => true,
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('products.show', $product), false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/orders', false);
    }
}
