<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmed;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\User;
use App\Support\Cart;
use App\Support\Loyalty;
use App\Support\Shipping;
use App\Support\VerificationRequirement;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart, private VerificationRequirement $verification, private Loyalty $loyalty, private Shipping $shipping) {}

    public function create(): View|RedirectResponse
    {
        if ($redirect = $this->verificationRedirect()) {
            return $redirect;
        }
        $items = $this->cart->items(request());
        if ($items->isEmpty()) {
            return to_route('cart.index')->with('warning', 'Your cart is empty.');
        }
        $checkoutToken = (string) Str::uuid();
        $tokens = array_slice(session('checkout_tokens', []), -4);
        $tokens[] = $checkoutToken;
        session(['checkout_tokens' => $tokens]);
        $subtotal = $this->cart->subtotal($items);
        $coupon = $this->couponFor($subtotal, request()->user()?->id, request()->user()?->email);

        return view('checkout.create', ['cart' => $items, 'subtotal' => (float) $subtotal, 'coupon' => $coupon, 'couponDiscount' => (float) ($coupon?->discountFor($subtotal) ?? 0), 'shippingRates' => ShippingRate::where('is_active', true)->orderBy('governorate')->get(), 'freeShippingThreshold' => $this->shipping->threshold(), 'checkoutToken' => $checkoutToken, 'availablePoints' => request()->user()?->loyalty_points ?? 0]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($redirect = $this->verificationRedirect()) {
            return $redirect;
        }
        $hadStoredItems = $request->session()->get('cart', []) !== [];
        $items = $this->cart->items($request, false);
        if ($items->isEmpty()) {
            return $hadStoredItems
                ? to_route('cart.index')->withErrors(['cart' => 'A product in your cart is no longer available.'])
                : to_route('cart.index')->with('warning', 'Your cart is empty.');
        }
        $validated = $request->validated();
        if (! in_array($validated['checkout_token'], $request->session()->get('checkout_tokens', []), true)) {
            throw ValidationException::withMessages(['cart' => 'This checkout session has expired. Please review your cart and try again.']);
        }
        $guestPlainToken = $request->user() ? null : Str::random(64);
        try {
            $order = DB::transaction(function () use ($request, $items, $validated, $guestPlainToken) {
                $user = $request->user() ? User::lockForUpdate()->findOrFail($request->user()->id) : null;
                $points = $user ? (int) ($validated['points_to_redeem'] ?? 0) : 0;
                $subtotal = '0.00';
                $lockedItems = [];
                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->find($item['product']->id);
                    $variant = $item['variant'] ? ProductVariant::lockForUpdate()->find($item['variant']->id) : null;
                    if (! $product?->is_active || ($variant && (! $variant->is_active || $variant->product_id !== $product->id))) {
                        throw ValidationException::withMessages(['cart' => 'A product option is no longer available.']);
                    }
                    $stock = $variant ? $variant->stock : $product->stock;
                    if ($item['quantity'] < 1 || $item['quantity'] > $stock) {
                        throw ValidationException::withMessages(['cart' => "{$product->name} is unavailable in the requested quantity."]);
                    }
                    $price = $variant ? $variant->effectivePrice() : $product->finalPrice();
                    $subtotal = bcadd($subtotal, bcmul($price, (string) $item['quantity'], 2), 2);
                    $lockedItems[] = compact('product', 'variant', 'price') + ['quantity' => $item['quantity']];
                }
                $coupon = $this->couponFor($subtotal, $request->user()?->id, $validated['customer_email'], true);
                $couponDiscount = $coupon?->discountFor($subtotal) ?? '0.00';
                $merchandisePayable = bcsub($subtotal, $couponDiscount, 2);
                $pointsDiscount = $this->loyalty->redemptionDiscount($points);
                if ($points > 0 && ($points % Loyalty::REDEMPTION_BLOCK !== 0 || $points > $user->loyalty_points || bccomp($pointsDiscount, $merchandisePayable, 2) === 1)) {
                    throw ValidationException::withMessages(['points_to_redeem' => 'Choose an available points amount in blocks of 100.']);
                }
                $shippingRate = ShippingRate::whereRaw('LOWER(governorate) = ?', [strtolower($validated['governorate'])])->where('is_active', true)->lockForUpdate()->first();
                if (! $shippingRate && ShippingRate::where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['governorate' => 'Please choose a supported delivery governorate.']);
                }
                $shipping = $this->shipping->feeFor($shippingRate, $merchandisePayable);
                $total = bcadd(bcsub($merchandisePayable, $pointsDiscount, 2), $shipping, 2);
                $order = Order::create(['user_id' => $user?->id, 'order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)), 'checkout_token' => $validated['checkout_token'], 'guest_access_token' => $guestPlainToken ? hash('sha256', $guestPlainToken) : null, 'status' => 'pending', 'subtotal' => $subtotal, 'coupon_code' => $coupon?->code, 'coupon_discount' => $couponDiscount, 'points_redeemed' => $points, 'points_discount' => $pointsDiscount, 'shipping_fee' => $shipping, 'total' => $total, 'customer_name' => $validated['customer_name'], 'customer_email' => $validated['customer_email'], 'phone' => $validated['phone'], 'address' => $this->formatDeliveryAddress($validated)]);
                if ($user) {
                    $this->loyalty->redeem($user, $order, $points, $merchandisePayable);
                }
                foreach ($lockedItems as $item) {
                    $line = bcmul($item['price'], (string) $item['quantity'], 2);
                    $order->items()->create(['product_id' => $item['product']->id, 'product_variant_id' => $item['variant']?->id, 'product_name' => $item['product']->name, 'variant_name' => $item['variant']?->name, 'variant_attributes' => $item['variant']?->attributes, 'variant_sku' => $item['variant']?->sku, 'price' => $item['price'], 'quantity' => $item['quantity'], 'subtotal' => $line]);
                    $stockModel = $item['variant'] ?: $item['product'];
                    $before = $stockModel->stock;
                    $stockModel->decrement('stock', $item['quantity']);
                    $item['product']->inventoryAdjustments()->create(['product_variant_id' => $item['variant']?->id, 'user_id' => $request->user()?->id, 'quantity_before' => $before, 'quantity_after' => $before - $item['quantity'], 'quantity_change' => -$item['quantity'], 'reason' => 'order', 'note' => "Order {$order->order_number}"]);
                }
                if ($coupon) {
                    $coupon->increment('times_used');
                    $coupon->usages()->create(['order_id' => $order->id, 'user_id' => $request->user()?->id, 'customer_email' => strtolower($validated['customer_email'])]);
                }

                return $order;
            }, 3);
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            $existing = Order::where('checkout_token', $validated['checkout_token'])->where(fn ($q) => $request->user() ? $q->where('user_id', $request->user()->id) : $q->where('customer_email', $validated['customer_email']))->first();
            if ($existing && $request->user()) {
                return to_route('orders.show', $existing)->with('warning', 'This order was already placed.');
            }
            Log::error('Checkout database failure', ['user_id' => $request->user()?->id, 'exception_type' => $e::class]);

            return back()->withInput()->with('error', 'We could not place your order. Please try again.');
        } catch (Throwable $e) {
            Log::error('Checkout failed', ['user_id' => $request->user()?->id, 'exception_type' => $e::class]);

            return back()->withInput()->with('error', 'We could not place your order. Please try again.');
        }
        $request->session()->forget(['cart', 'checkout_tokens', 'coupon_code']);
        try {
            Mail::to($order->customer_email)->queue(new OrderConfirmed($order->load('items'), $guestPlainToken));
        } catch (Throwable $e) {
            Log::warning('Order confirmation email could not be queued.', ['order_id' => $order->id, 'exception_type' => $e::class]);
        }

        return $request->user() ? to_route('orders.show', $order)->with('success', 'Your order has been placed.') : redirect()->route('guest.orders.show', [$order, $guestPlainToken])->with('success', 'Your order has been placed. Save this secure link.');
    }

    private function couponFor(string $subtotal, ?int $userId, ?string $email, bool $lock = false): ?Coupon
    {
        $code = session('coupon_code');
        if (! $code) {
            return null;
        }
        $query = Coupon::where('code', $code);
        if ($lock) {
            $query->lockForUpdate();
        } $coupon = $query->first();
        if (! $coupon?->isAvailable($subtotal)) {
            return null;
        }

        return $coupon;
    }

    private function verificationRedirect(): ?RedirectResponse
    {
        $user = request()->user();

        return $user && $this->verification->required() && ! $user->hasVerifiedEmail() ? to_route('verification.notice') : null;
    }

    private function formatDeliveryAddress(array $data): string
    {
        return collect(['Governorate' => $data['governorate'], 'City / Area' => $data['city_area'], 'Street' => $data['street'], 'Building' => $data['building_number'], 'Floor / Apartment' => $data['floor_apartment'] ?? null, 'Landmark' => $data['landmark'] ?? null, 'Delivery Notes' => $data['delivery_notes'] ?? null])->filter(fn ($value) => filled($value))->map(fn ($value, $label) => $label.': '.trim($value))->implode(PHP_EOL);
    }
}
