<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $product->variants()->create($this->data($request));

        return back()->with('success', 'Variant added.');
    }

    public function update(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);
        $before = $variant->stock;
        $variant->update($this->data($request, $variant));
        if ($before !== $variant->stock) {
            InventoryAdjustment::create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'user_id' => $request->user()->id, 'quantity_before' => $before, 'quantity_after' => $variant->stock, 'quantity_change' => $variant->stock - $before, 'reason' => 'manual adjustment', 'note' => 'Updated through variant editor']);
        }

        return back()->with('success', 'Variant updated.');
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);
        if ($variant->stock > 0) {
            return back()->with('warning', 'Set stock to zero before deleting a variant.');
        } $variant->delete();

        return back()->with('success', 'Variant deleted.');
    }

    private function data(Request $request, ?ProductVariant $variant = null): array
    {
        $data = $request->validate(['sku' => ['required', 'string', 'max:100', Rule::unique('product_variants')->ignore($variant)], 'name' => ['required', 'string', 'max:150'], 'attributes' => ['nullable', 'json'], 'price' => ['nullable', 'numeric', 'min:0.01'], 'stock' => ['required', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean']]);
        $data['attributes'] = filled($data['attributes'] ?? null) ? json_decode($data['attributes'], true, flags: JSON_THROW_ON_ERROR) : null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
