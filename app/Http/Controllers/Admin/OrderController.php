<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $q = Order::with('user')->latest();
        $q->when($request->status, fn ($query, $status) => $query->where('status', $status));

        return view('admin.orders.index', ['orders' => $q->paginate(20)->withQueryString()]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('user', 'items')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(Order::STATUSES)]]);
        $order->update($data);

        return back()->with('success', 'Order status updated.');
    }
}
