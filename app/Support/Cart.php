<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Cart
{
    public function items(Request $request, bool $cleanSession = true): Collection
    {
        $stored = $request->session()->get('cart', []);
        $lines = collect($stored)->map(function ($quantity, $key) {
            $parts = str_contains((string) $key, ':') ? explode(':', (string) $key, 2) : [(string) $key, null];

            return ['key' => (string) $key, 'product_id' => (int) $parts[0], 'variant_id' => filled($parts[1] ?? null) ? (int) $parts[1] : null, 'quantity' => (int) $quantity];
        });
        $products = Product::with('category')->whereIn('id', $lines->pluck('product_id'))->get()->keyBy('id');
        $variants = ProductVariant::whereIn('id', $lines->pluck('variant_id')->filter())->get()->keyBy('id');
        $clean = [];
        $resolved = collect();

        foreach ($lines as $line) {
            $product = $products->get($line['product_id']);
            $variant = $line['variant_id'] ? $variants->get($line['variant_id']) : null;
            if (! $product?->is_active || ($line['variant_id'] && (! $variant?->is_active || $variant->product_id !== $product->id))) {
                continue;
            }
            $available = $variant ? $variant->stock : $product->stock;
            $quantity = $cleanSession ? min(max(0, $line['quantity']), $available) : max(0, $line['quantity']);
            if ($quantity < 1) {
                continue;
            }
            $clean[$line['key']] = $quantity;
            $resolved->push(['key' => $line['key'], 'product' => $product, 'variant' => $variant, 'quantity' => $quantity, 'unit_price' => $variant ? $variant->effectivePrice() : $product->finalPrice()]);
        }
        if ($cleanSession) {
            $request->session()->put('cart', $clean);
        }

        return $resolved;
    }

    public function subtotal(Collection $items): string
    {
        return $items->reduce(fn (string $total, array $item) => bcadd($total, bcmul($item['unit_price'], (string) $item['quantity'], 2), 2), '0.00');
    }

    public function key(Product $product, ?ProductVariant $variant): string
    {
        return $variant ? $product->id.':'.$variant->id : (string) $product->id;
    }
}
