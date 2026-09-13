@extends('layouts.admin')

@section('content')
    <x-admin-page-header eyebrow="Orders" :title="$order->order_number" description="Review customer, fulfilment and order details.">
        <x-slot:action>
            <form class="admin-order-status" method="POST" action="{{ route('admin.orders.update', $order) }}">
                @csrf
                @method('PATCH')
                <label class="visually-hidden" for="status">Order status</label>
                <select id="status" class="form-select" name="status">
                    @foreach(App\Models\Order::STATUSES as $status)
                        <option @selected($order->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn btn-dark" type="submit">Update</button>
            </form>
        </x-slot:action>
    </x-admin-page-header>

    <div class="admin-order-meta">
        <div><small>Status</small><strong class="text-capitalize">{{ $order->status }}</strong></div>
        <div><small>Placed</small><strong>{{ $order->created_at->format('d M Y') }}</strong></div>
        <a href="{{ route('admin.orders.index') }}">Back to orders</a>
    </div>

    <div class="admin-order-layout">
        <section class="admin-order-panel">
            <h2>Order items</h2>
            <div class="table-responsive">
                <table class="table align-middle admin-table">
                    <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr><td><div class="admin-order-item"><div class="admin-order-item__media">@if($item->product?->imageUrl())<img src="{{ $item->product->imageUrl() }}" alt="{{ $item->product_name }}">@else<span>WINNER</span>@endif</div><div><strong>{{ $item->product_name }}</strong>@if($item->variant_name)<small>{{ $item->variant_name }}</small>@endif</div></div></td><td>{{ number_format($item->price, 2) }} EGP</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->subtotal, 2) }} EGP</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="3">Total</th><th>{{ number_format($order->total, 2) }} EGP</th></tr></tfoot>
                </table>
            </div>
        </section>

        <aside class="admin-order-panel">
            <h2>Customer information</h2>
            <p>{{ $order->customer_name }}<br><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a><br><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></p>
            <h3>Delivery address</h3>
            <p class="formatted-address">{{ $order->address }}</p>
            <h3>Payment summary</h3>
            <dl>
                <div><dt>Subtotal</dt><dd>{{ number_format($order->subtotal, 2) }} EGP</dd></div>
                @if((float)$order->coupon_discount > 0)<div><dt>Coupon {{ $order->coupon_code }}</dt><dd>-{{ number_format($order->coupon_discount, 2) }} EGP</dd></div>@endif
                @if($order->first_order_offer_label)<div><dt>{{ $order->first_order_offer_label }}</dt><dd>@if((float)$order->first_order_discount > 0)-{{ number_format($order->first_order_discount, 2) }} EGP@else Applied @endif</dd></div>@endif
                @if($order->points_redeemed > 0)<div><dt>Rewards ({{ number_format($order->points_redeemed) }} points)</dt><dd>-{{ number_format($order->points_discount, 2) }} EGP</dd></div>@endif
                <div><dt>Shipping</dt><dd>{{ number_format($order->shipping_fee, 2) }} EGP</dd></div>
                <div><dt>Total</dt><dd>{{ number_format($order->total, 2) }} EGP</dd></div>
                <div><dt>Points earned</dt><dd>{{ number_format($order->points_earned) }}</dd></div>
            </dl>
        </aside>
    </div>
    @if($order->cancellation_status === 'pending')<section class="admin-order-panel mt-4"><h2>Cancellation request</h2><p>{{ $order->cancellation_reason }}</p><div class="d-flex gap-2"><form method="POST" action="{{ route('admin.orders.cancellation.review', $order) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="approved"><button class="btn btn-dark">Approve and restore stock</button></form><form method="POST" action="{{ route('admin.orders.cancellation.review', $order) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="rejected"><button class="btn btn-outline-dark">Reject</button></form></div></section>@endif
@endsection
