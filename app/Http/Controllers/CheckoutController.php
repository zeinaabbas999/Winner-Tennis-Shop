<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function create(): View
    {
        abort_if(empty(session('cart', [])), 404, 'Your bag is empty.');

        return view('checkout.create');
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return to_route('cart.index')->with('warning', 'Your bag is empty.');
        }
        try {
            $order = DB::transaction(function () use ($request, $cart) {
                $products = Product::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
                if ($products->count() !== count($cart)) {
                    throw ValidationException::withMessages(['cart' => 'A product in your bag is no longer available.']);
                }
                $subtotal = '0.00';
                foreach ($cart as $id => $quantity) {
                    $product = $products[$id];
                    if (! $product->is_active || $quantity < 1 || $quantity > $product->stock) {
                        throw ValidationException::withMessages(['cart' => "{$product->name} is unavailable in the requested quantity."]);
                    }
                    $subtotal = bcadd($subtotal, bcmul((string) $product->price, (string) $quantity, 2), 2);
                }
                $order = $request->user()->orders()->create(array_merge($request->validated(), ['order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(str()->random(8)), 'status' => 'pending', 'subtotal' => $subtotal, 'total' => $subtotal]));
                foreach ($cart as $id => $quantity) {
                    $product = $products[$id];
                    $line = bcmul((string) $product->price, (string) $quantity, 2);
                    $order->items()->create(['product_id' => $id, 'product_name' => $product->name, 'price' => $product->price, 'quantity' => $quantity, 'subtotal' => $line]);
                    $product->decrement('stock', $quantity);
                }

                return $order;
            }, 3);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Checkout failed', ['user_id' => $request->user()->id, 'exception' => $e]);

            return back()->withInput()->with('error', 'We could not place your order. Please try again.');
        }
        $request->session()->forget('cart');

        return to_route('orders.show', $order)->with('success', 'Your order has been placed.');
    }
}
