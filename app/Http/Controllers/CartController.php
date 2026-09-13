<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function index(Request $request): View
    {
        $items = $this->cart->items($request);
        $subtotal = $this->cart->subtotal($items);
        $coupon = Coupon::where('code', $request->session()->get('coupon_code'))->first();
        if (! $coupon?->isAvailable($subtotal)) {
            $coupon = null;
        }

        return view('cart.index', ['cart' => $items, 'subtotal' => (float) $subtotal, 'coupon' => $coupon, 'couponDiscount' => (float) ($coupon?->discountFor($subtotal) ?? 0)]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1'], 'variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where('product_id', $product->id)->where('is_active', true)]]);
        $variant = isset($data['variant_id']) ? $product->variants()->findOrFail($data['variant_id']) : null;
        if ($product->variants()->where('is_active', true)->exists() && ! $variant) {
            return back()->withErrors(['variant_id' => 'Please choose a product option.']);
        }
        $available = $variant ? $variant->stock : $product->stock;
        if ($available < 1) {
            return back()->withErrors(['quantity' => 'This product is out of stock.']);
        }
        $key = $this->cart->key($product, $variant);
        $stored = $request->session()->get('cart', []);
        $quantity = ($stored[$key] ?? 0) + $data['quantity'];
        if ($quantity > $available) {
            return back()->withErrors(['quantity' => "Only {$available} item(s) are available."]);
        }
        $stored[$key] = $quantity;
        $request->session()->put('cart', $stored);

        return back()->with('success', 'Added to your cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1'], 'line_key' => ['nullable', 'string', 'max:100']]);
        $key = $data['line_key'] ?? (string) $product->id;
        $item = $this->cart->items($request)->firstWhere('key', $key);
        if (! $item || $item['product']->id !== $product->id || $data['quantity'] > ($item['variant']?->stock ?? $product->stock)) {
            return back()->withErrors(['quantity' => 'The requested quantity is unavailable.']);
        }
        $stored = $request->session()->get('cart', []);
        $stored[$key] = $data['quantity'];
        $request->session()->put('cart', $stored);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $key = $request->validate(['line_key' => ['nullable', 'string', 'max:100']])['line_key'] ?? (string) $product->id;
        $stored = $request->session()->get('cart', []);
        unset($stored[$key]);
        $request->session()->put('cart', $stored);

        return back()->with('success', 'Item removed.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(['cart', 'coupon_code']);

        return back()->with('success', 'Cart cleared.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $code = strtoupper($request->validate(['code' => ['required', 'string', 'max:50']])['code']);
        $items = $this->cart->items($request);
        $subtotal = $this->cart->subtotal($items);
        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon?->isAvailable($subtotal)) {
            return back()->withErrors(['code' => 'This coupon is unavailable or its requirements are not met.']);
        }
        $request->session()->put('coupon_code', $coupon->code);

        return back()->with('success', 'Coupon applied.');
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget('coupon_code');

        return back()->with('success', 'Coupon removed.');
    }
}
