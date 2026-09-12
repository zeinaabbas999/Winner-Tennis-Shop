<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderCancellationController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->canRequestCancellation(), 422);
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $order->update(['cancellation_requested_at' => now(), 'cancellation_reason' => $data['reason'], 'cancellation_status' => 'pending']);

        return back()->with('success', 'Your cancellation request has been sent for review.');
    }
}
