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
            'newArrivals' => Product::with('category')->where('is_active', true)->latest()->limit(8)->get(),
            'categories' => Category::whereHas('products', fn ($query) => $query->where('is_active', true))
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->limit(6)
                ->get(),
        ]);
    }
}
