<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->resolvedCart($request);

        return view('cart.index', ['cart' => $cart, 'subtotal' => $cart->sum(fn ($item) => $item['product']->price * $item['quantity'])]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:'.$product->stock]]);
        if ($product->stock < 1) {
            return back()->with('warning', 'This product is out of stock.');
        }
        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$product->id] ?? 0) + $data['quantity'];
        if ($quantity > $product->stock) {
            return back()->withErrors(['quantity' => 'Only '.$product->stock.' item(s) are available.']);
        }
        $cart[$product->id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Added to your cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        if (! $product->is_active || $data['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => 'The requested quantity is unavailable.']);
        }
        $cart = $request->session()->get('cart', []);
        $cart[$product->id] = $data['quantity'];
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Item removed.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    private function resolvedCart(Request $request)
    {
        $stored = $request->session()->get('cart', []);
        $products = Product::with('category')->where('is_active', true)->whereIn('id', array_keys($stored))->get()->keyBy('id');
        $clean = [];
        foreach ($stored as $id => $quantity) {
            if ($products->has($id)) {
                $clean[$id] = min((int) $quantity, max(0, $products[$id]->stock));
            }
        }
        $clean = array_filter($clean, fn ($quantity) => $quantity > 0);
        $request->session()->put('cart', $clean);

        return collect($clean)->map(fn ($quantity, $id) => ['product' => $products[$id], 'quantity' => $quantity]);
    }
}
