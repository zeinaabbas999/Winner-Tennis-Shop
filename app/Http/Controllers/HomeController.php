<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'featuredProducts' => Product::with('category')->withCount('reviews')->withAvg('reviews', 'rating')->withSum('orderItems as sales_quantity', 'quantity')->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')->where('is_active', true)->where('is_featured', true)->latest()->limit(8)->get(),
            'newArrivals' => Product::with('category')->withCount('reviews')->withAvg('reviews', 'rating')->withSum('orderItems as sales_quantity', 'quantity')->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')->where('is_active', true)->latest()->limit(8)->get(),
            'rackets' => Product::with('category')->withCount('reviews')->withAvg('reviews', 'rating')->withSum('orderItems as sales_quantity', 'quantity')->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')->where('is_active', true)->whereHas('category', fn ($query) => $query->where('slug', 'tennis-rackets'))->limit(4)->get(),
            'categories' => Category::whereHas('products', fn ($query) => $query->where('is_active', true))
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->limit(6)
                ->get(),
        ]);
    }
}
