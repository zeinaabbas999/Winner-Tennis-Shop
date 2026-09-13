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

    public function accessories(Request $request): View
    {
        return $this->catalogue(
            $request,
            categorySlugs: ['dampeners', 'grips-and-overgrips', 'other-tennis-accessories'],
            title: 'Accessories',
            description: 'Dampeners, overgrips and practical court accessories in one collection.',
        );
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'variants' => fn ($query) => $query->where('is_active', true)])->loadCount('reviews')->loadAvg('reviews', 'rating');
        $reviews = $product->reviews()->with('user')->latest()->paginate(8);
        $userReview = $request->user()?->reviews()->whereBelongsTo($product)->first();
        $recentIds = array_values(array_filter($request->session()->get('recently_viewed', []), fn ($id) => $id !== $product->id));
        array_unshift($recentIds, $product->id);
        $request->session()->put('recently_viewed', array_slice($recentIds, 0, 8));
        $related = Product::query()->with('category')->withCount('reviews')->withAvg('reviews', 'rating')->withSum('orderItems as sales_quantity', 'quantity')->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')
            ->where('is_active', true)->whereKeyNot($product->id)
            ->orderByRaw('category_id = ? DESC', [$product->category_id])
            ->orderByRaw('brand = ? DESC', [$product->brand])
            ->orderByRaw('ABS(price - ?) ASC', [$product->price])->limit(4)->get();
        $recentlyViewed = Product::query()->with('category')->withCount('reviews')->withAvg('reviews', 'rating')->withSum('orderItems as sales_quantity', 'quantity')->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')->where('is_active', true)
            ->whereIn('id', array_slice($recentIds, 1))->get()
            ->sortBy(fn ($item) => array_search($item->id, $recentIds, true))->take(4);

        return view('products.show', [
            'product' => $product,
            'reviews' => $reviews,
            'userReview' => $userReview,
            'canReview' => $request->user()?->hasPurchasedProduct($product) === true,
            'related' => $related,
            'recentlyViewed' => $recentlyViewed,
        ]);
    }

    private function catalogue(
        Request $request,
        ?Category $category = null,
        array $categorySlugs = [],
        ?string $title = null,
        ?string $description = null,
    ): View {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', Rule::in(self::FILTER_BRANDS)],
            'sort' => ['nullable', Rule::in(['price_asc', 'price_desc'])],
        ]);

        $query = Product::query()->with('category')->withCount('reviews')->withAvg('reviews', 'rating')->withSum('orderItems as sales_quantity', 'quantity')->withSum(['variants as variants_stock' => fn ($q) => $q->where('is_active', true)], 'stock')->where('is_active', true);
        $query->when($category, fn (Builder $builder) => $builder->whereBelongsTo($category));
        $query->when($categorySlugs !== [], fn (Builder $builder) => $builder->whereHas(
            'category',
            fn (Builder $categoryQuery) => $categoryQuery->whereIn('slug', $categorySlugs),
        ));
        $query->when(! $category && isset($validated['category']), fn (Builder $builder) => $builder->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $validated['category'])));
        if ($term = trim($validated['search'] ?? '')) {
            $normalized = strtolower($term);
            $categoryTerms = match ($normalized) {
                'bag', 'bags', 'tennis bag', 'tennis bags' => ['tennis-bags'],
                'ball', 'balls', 'tennis ball', 'tennis balls' => ['tennis-balls'],
                'racket', 'rackets', 'racquet', 'racquets' => ['tennis-rackets'],
                'overgrip', 'overgrips', 'grip', 'grips' => ['grips-and-overgrips'],
                'string', 'strings' => ['strings'],
                'dampener', 'dampeners' => ['dampeners'],
                'accessory', 'accessories' => ['dampeners', 'grips-and-overgrips', 'other-tennis-accessories'],
                default => [],
            };
            $query->where(function (Builder $search) use ($term, $categoryTerms) {
                $search->where('name', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('specs', 'like', "%{$term}%")
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery
                        ->where('name', 'like', "%{$term}%")
                        ->when($categoryTerms !== [], fn (Builder $query) => $query->orWhereIn('slug', $categoryTerms)));
            })->orderByRaw('CASE WHEN LOWER(name) = ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 WHEN LOWER(brand) = ? THEN 2 ELSE 3 END', [$normalized, "%{$normalized}%", $normalized]);
        }
        $query->when($validated['brand'] ?? null, fn (Builder $builder, string $brand) => $builder->where('brand', $brand));

        match ($validated['sort'] ?? null) {
            'price_asc' => $query->orderByRaw('price * (100 - discount_percent) / 100'),
            'price_desc' => $query->orderByRaw('price * (100 - discount_percent) / 100 DESC'),
            default => $category || $categorySlugs !== [] || $term
                ? $query->orderBy('name')
                : $query->orderByRaw("CASE (SELECT slug FROM categories WHERE categories.id = products.category_id) WHEN 'tennis-rackets' THEN 1 WHEN 'tennis-balls' THEN 2 WHEN 'tennis-bags' THEN 3 WHEN 'strings' THEN 4 WHEN 'dampeners' THEN 5 WHEN 'grips-and-overgrips' THEN 6 WHEN 'other-tennis-accessories' THEN 7 ELSE 8 END")->orderBy('products.name'),
        };

        $showBrandFilter = ! $category || in_array($category->slug, ['tennis-rackets', 'tennis-balls', 'tennis-bags', 'strings'], true);

        return view('products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'category' => $category,
            'brands' => $showBrandFilter ? collect(self::FILTER_BRANDS) : collect(),
            'catalogueTitle' => $title ?? $category?->name ?? 'Shop tennis',
            'catalogueDescription' => $description ?? $category?->description ?? 'Equipment selected for training, competition and club play.',
            'clearUrl' => $categorySlugs !== [] ? route('shop.accessories') : ($category ? route('shop.category', $category) : route('products.index')),
        ]);
    }
}
