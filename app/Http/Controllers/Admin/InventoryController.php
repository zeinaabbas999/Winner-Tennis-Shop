<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['stock' => ['nullable', Rule::in(['low', 'out'])]])['stock'] ?? null;
        $products = Product::with('category')->when($filter === 'low', fn ($q) => $q->whereBetween('stock', [1, 5]))->when($filter === 'out', fn ($q) => $q->where('stock', 0))->orderBy('stock')->paginate(20)->withQueryString();
        $history = InventoryAdjustment::with(['product', 'variant', 'user'])->latest()->limit(20)->get();

        return view('admin.inventory.index', compact('products', 'history', 'filter'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['stock' => ['required', 'integer', 'min:0'], 'reason' => ['required', Rule::in(['manual adjustment', 'restock', 'correction'])], 'note' => ['nullable', 'string', 'max:500']]);
        DB::transaction(function () use ($request, $product, $data) {
            $locked = Product::lockForUpdate()->findOrFail($product->id);
            $before = $locked->stock;
            $locked->update(['stock' => $data['stock']]);
            $locked->inventoryAdjustments()->create(['user_id' => $request->user()->id, 'quantity_before' => $before, 'quantity_after' => $data['stock'], 'quantity_change' => $data['stock'] - $before, 'reason' => $data['reason'], 'note' => $data['note'] ?? null]);
        });

        return back()->with('success', 'Inventory updated.');
    }
}
