<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = Product::with('category')->latest();
        $q->when($request->string('search')->trim()->value(), fn ($query, $term) => $query->where('name', 'like', "%{$term}%"));

        return view('admin.products.index', ['products' => $q->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(SaveProductRequest $request): RedirectResponse
    {
        Product::create($this->data($request));

        return to_route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(SaveProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->data($request, $product);
        if ($request->hasFile('image') && $product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->update($data);

        return to_route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists() || $product->reviews()->exists()) {
            return back()->with('warning', 'Products with order or review history cannot be deleted; make this product inactive instead.');
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    private function data(SaveProductRequest $request, ?Product $product = null): array
    {
        $data = $request->safe()->except('image');
        if (isset($data['specs'])) {
            $data['specs'] = array_filter($data['specs'], fn ($value) => filled($value));
            $data['specs'] = $data['specs'] ?: null;
        }
        $data['is_active'] = $request->boolean('is_active');
        $data['discount_percent'] = $data['discount_percent'] ?? 0;
        $base = Str::slug($data['name']) ?: 'product';
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->when($product, fn ($q) => $q->whereKeyNot($product->id))->exists()) {
            $slug = $base.'-'.$i++;
        }
        $data['slug'] = $slug;
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        return $data;
    }
}
