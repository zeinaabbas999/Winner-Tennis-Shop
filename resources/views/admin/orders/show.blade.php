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
                            <tr><td>{{ $item->product_name }}</td><td>{{ number_format($item->price, 2) }} EGP</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->subtotal, 2) }} EGP</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="3">Total</th><th>{{ number_format($order->total, 2) }} EGP</th></tr></tfoot>
                </table>
            </div>
        </section>

        <aside class="admin-order-panel">
            <h2>Customer information</h2>
            <p>{{ $order->customer_name }}<br>{{ $order->customer_email }}<br>{{ $order->phone }}</p>
            <h3>Delivery address</h3>
            <p>{{ $order->address }}</p>
            <h3>Payment summary</h3>
            <dl>
                <div><dt>Subtotal</dt><dd>{{ number_format($order->subtotal, 2) }} EGP</dd></div>
                <div><dt>Total</dt><dd>{{ number_format($order->total, 2) }} EGP</dd></div>
            </dl>
        </aside>
    </div>
@endsection
