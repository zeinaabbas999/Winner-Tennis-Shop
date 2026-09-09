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
            'newArrivals' => Product::with('category')->withCount('reviews')->withAvg('reviews', 'rating')->where('is_active', true)->latest()->limit(8)->get(),
            'rackets' => Product::with('category')->withCount('reviews')->withAvg('reviews', 'rating')->where('is_active', true)->whereHas('category', fn ($query) => $query->where('slug', 'tennis-rackets'))->limit(4)->get(),
            'categories' => Category::whereHas('products', fn ($query) => $query->where('is_active', true))
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->limit(6)
                ->get(),
        ]);
    }
}
