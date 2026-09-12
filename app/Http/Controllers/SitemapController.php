<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        return response()
            ->view('sitemap', [
                'categories' => Category::query()->whereHas('products', fn ($query) => $query->where('is_active', true))->get(),
                'products' => Product::query()->where('is_active', true)->get(['slug', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml');
    }
}
