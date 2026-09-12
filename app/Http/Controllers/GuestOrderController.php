<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class GuestOrderController extends Controller
{
    public function show(Order $order, string $token): View
    {
        abort_unless($order->user_id === null && $order->guest_access_token && hash_equals($order->guest_access_token, hash('sha256', $token)), 404);

        return view('orders.show', ['order' => $order->load('items.product')]);
    }
}
