<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const FILTER_BRANDS = ['Wilson', 'Yonex', 'Dunlop', 'Babolat'];

    public function index(Request $request): View
    {
        return $this->catalogue($request);
    }

    public function category(Request $request, Category $category): View
    {
        return $this->catalogue($request, $category);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('products.show', ['product' => $product->load('category')]);
    }

    private function catalogue(Request $request, ?Category $category = null): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', Rule::in(self::FILTER_BRANDS)],
            'sort' => ['nullable', Rule::in(['price_asc', 'price_desc'])],
        ]);

        $query = Product::query()->with('category')->where('is_active', true);
        $query->when($category, fn (Builder $builder) => $builder->whereBelongsTo($category));
        $query->when(! $category && isset($validated['category']), fn (Builder $builder) => $builder->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $validated['category'])));
        $query->when($validated['search'] ?? null, fn (Builder $builder, string $term) => $builder->where(fn (Builder $search) => $search
            ->where('name', 'like', "%{$term}%")
            ->orWhere('brand', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")));
        $query->when($validated['brand'] ?? null, fn (Builder $builder, string $brand) => $builder->where('brand', $brand));

        match ($validated['sort'] ?? null) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        $showBrandFilter = ! $category || in_array($category->slug, ['tennis-rackets', 'tennis-balls', 'tennis-bags', 'strings'], true);

        return view('products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'category' => $category,
            'brands' => $showBrandFilter ? collect(self::FILTER_BRANDS) : collect(),
        ]);
    }
}
