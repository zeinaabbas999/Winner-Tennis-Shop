<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnExchangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReturnExchangeRequestController extends Controller
{
    public function storeFromPolicy(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['return', 'exchange'], true), 404);
        $item = OrderItem::with('order')->findOrFail($request->integer('order_item_id'));
        abort_unless($item->order->user_id === $request->user()->id, 403);

        $request->merge(['type' => $type]);

        return $this->store($request, $item->order);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['delivered', 'completed'], true), 422);
        $data = $request->validate([
            'type' => ['required', Rule::in(['return', 'exchange'])],
            'order_item_id' => ['required', Rule::exists('order_items', 'id')->where('order_id', $order->id), Rule::unique('return_exchange_requests', 'order_item_id')->where('type', $request->input('type'))],
            'reason' => ['required', 'string', 'max:1500'],
        ]);
        ReturnExchangeRequest::create($data + ['order_id' => $order->id, 'user_id' => $request->user()->id]);

        return back()->with('success', 'Your request has been submitted for review.');
    }
}
