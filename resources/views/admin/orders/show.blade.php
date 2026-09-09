@extends('layouts.admin') @section('content')
    <a href="{{ route('admin.orders.index') }}">
        ← Orders</a>
    <div class="d-flex justify-content-between align-items-end mt-3">
        <div><small>ORDER</small>
            <h1>{{ $order->order_number }}</h1>
        </div>
        <form method="POST" action="{{ route('admin.orders.update', $order) }}">@csrf @method('PATCH')<div
                class="input-group"><select class="form-select" name="status">
                    @foreach (\App\Models\Order::STATUSES as $status)
                        <option @selected($order->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn btn-dark">Update</button>
            </div>
        </form>
    </div>
    <div class="row g-5 mt-4">
        <div class="col-lg-8">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->subtotal, 2) }} EGP</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>{{ number_format($order->total, 2) }} EGP</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <aside class="col-lg-4">
            <h2 class="h5">Delivery</h2>
            <p>{{ $order->customer_name }}<br>{{ $order->customer_email }}<br>{{ $order->phone }}</p>
            <p>{{ $order->address }}</p>
        </aside>
    </div>
@endsection
