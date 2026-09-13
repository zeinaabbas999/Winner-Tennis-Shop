<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChanged;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Loyalty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private Loyalty $loyalty) {}

    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', Rule::in(Order::STATUSES)]]);
        $q = Order::with('user')->latest();
        $q->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));

        return view('admin.orders.index', ['orders' => $q->paginate(20)->withQueryString()]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('user', 'items.product')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(Order::STATUSES)]]);
        $changed = $order->status !== $data['status'];
        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);
            $lockedOrder->status = $data['status'];
            $lockedOrder->save();
            if ($data['status'] === 'delivered') {
                $this->loyalty->awardForDelivered($lockedOrder);
            } elseif ($data['status'] === 'cancelled') {
                $this->loyalty->reverseForCancellation($lockedOrder);
            }
        });
        if ($changed) {
            try {
                Mail::to($order->customer_email)->queue(new OrderStatusChanged($order, 'Your order status has changed.'));
            } catch (\Throwable $e) {
                Log::warning('Order status email could not be queued.', ['order_id' => $order->id, 'exception_type' => $e::class]);
            }
        }

        return back()->with('success', 'Order status updated.');
    }

    public function reviewCancellation(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])]]);
        abort_unless($order->cancellation_status === 'pending', 422);

        DB::transaction(function () use ($request, $order, $data) {
            $lockedOrder = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->cancellation_status !== 'pending') {
                abort(422);
            }
            if ($data['decision'] === 'approved') {
                foreach ($lockedOrder->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if (! $product) {
                        continue;
                    }
                    $stockModel = $item->product_variant_id ? ProductVariant::lockForUpdate()->find($item->product_variant_id) : $product;
                    if (! $stockModel) {
                        continue;
                    }
                    $before = $stockModel->stock;
                    $stockModel->increment('stock', $item->quantity);
                    InventoryAdjustment::create(['product_id' => $product->id, 'product_variant_id' => $item->product_variant_id, 'user_id' => $request->user()->id, 'quantity_before' => $before, 'quantity_after' => $before + $item->quantity, 'quantity_change' => $item->quantity, 'reason' => 'cancellation/restoration', 'note' => "Order {$lockedOrder->order_number}"]);
                }
                $lockedOrder->status = 'cancelled';
                $this->loyalty->reverseForCancellation($lockedOrder);
            }
            $lockedOrder->cancellation_status = $data['decision'];
            $lockedOrder->cancellation_reviewed_at = now();
            $lockedOrder->cancellation_reviewed_by = $request->user()->id;
            $lockedOrder->save();
        });

        try {
            Mail::to($order->customer_email)->queue(new OrderStatusChanged($order->fresh(), 'Your cancellation request was '.$data['decision'].'.'));
        } catch (\Throwable $e) {
            Log::warning('Cancellation email could not be queued.', ['order_id' => $order->id, 'exception_type' => $e::class]);
        }

        return back()->with('success', 'Cancellation request '.$data['decision'].'.');
    }
}
