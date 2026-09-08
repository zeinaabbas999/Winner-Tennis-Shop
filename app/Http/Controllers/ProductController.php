<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'], 'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
        ]);
        $query = Product::with('category')->where('is_active', true);
        $query->when($validated['search'] ?? null, fn ($q, $term) => $q->where(fn ($inner) => $inner->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")));
        $query->when($validated['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($category) => $category->where('slug', $slug)));
        $query->when($validated['min_price'] ?? null, fn ($q, $price) => $q->where('price', '>=', $price));
        $query->when($validated['max_price'] ?? null, fn ($q, $price) => $q->where('price', '<=', $price));
        match ($validated['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'), 'price_desc' => $query->orderByDesc('price'), default => $query->latest()
        };

        return view('products.index', ['products' => $query->paginate(12)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('products.show', ['product' => $product->load('category')]);
    }
}
