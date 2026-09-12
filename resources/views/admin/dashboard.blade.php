@extends('layouts.admin') @section('content')
    <x-admin-page-header eyebrow="Control room" title="Overview" description="A concise view of catalogue and order activity.">
        <x-slot:action><span>Welcome, {{ auth()->user()->name }}</span></x-slot:action></x-admin-page-header>
    <div class="admin-stats">
        @foreach (['products' => 'Products', 'categories' => 'Categories', 'orders' => 'Orders', 'customers' => 'Customers', 'pending' => 'Pending', 'lowStock' => 'Low stock'] as $key => $label)
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
    <div class="row g-4 mt-1"><section class="col-lg-7 admin-section"><h2>Orders over 14 days</h2><div class="analytics-bars">@forelse($ordersByDay as $point)<div title="{{ $point->orders }} orders, {{ number_format($point->revenue,2) }} EGP"><span style="height:{{ max(8,min(100,$point->orders*12)) }}%"></span><small>{{ \Carbon\Carbon::parse($point->day)->format('d M') }}</small></div>@empty<p>No recent order data.</p>@endforelse</div></section><section class="col-lg-5 admin-section"><h2>Top-selling products</h2><ol class="list-unstyled">@foreach($topProducts as $item)<li class="d-flex justify-content-between py-2"><span>{{ $item->product_name }}</span><strong>{{ $item->units }} units</strong></li>@endforeach</ol></section></div>
    <section class="admin-section"><h2>Low-stock watch</h2><div class="d-flex flex-wrap gap-3">@foreach($lowStockProducts as $product)<a href="{{ route('admin.products.edit',$product) }}">{{ $product->name }} · {{ $product->stock }}</a>@endforeach</div></section>
@endsection
