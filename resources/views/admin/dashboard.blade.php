@extends('layouts.admin') @section('content')
    <x-admin-page-header eyebrow="Control room" title="Overview" description="A concise view of catalogue and order activity.">
        <x-slot:action><span>Welcome, {{ auth()->user()->name }}</span></x-slot:action></x-admin-page-header>
    <div class="admin-stats">
        @foreach (['products' => 'Products', 'categories' => 'Categories', 'orders' => 'Orders', 'pending' => 'Pending', 'lowStock' => 'Low stock'] as $key => $label)
            <div class="stat"><small>{{ $label }}</small><strong>{{ $stats[$key] }}</strong></div>
        @endforeach
        <div class="stat stat-revenue">
            <small>Revenue</small><strong>{{ number_format($stats['revenue'], 2) }} <em>EGP</em></strong>
        </div>
    </div>
    <section class="admin-section">
        <h2>Recent orders</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->customer_name }}</td>
                            <td class="text-capitalize">{{ $order->status }}</td>
                            <td>{{ number_format($order->total, 2) }} EGP</td>
                    </tr>@empty<tr>
                            <td colspan="4">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
