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
            <table class="table align-middle admin-table">
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
    @php($maxOrders = max(1, $ordersByDay->max('orders')))
    <div class="admin-analytics-grid">
        <section class="admin-section admin-chart-section">
            <header><h2>Orders over 14 days</h2><p>Daily order count, including days with no orders.</p></header>
            <div class="analytics-bars" aria-label="Orders over the last 14 days">
                @foreach($ordersByDay as $point)
                    <div title="{{ $point->orders }} orders, {{ number_format($point->revenue,2) }} EGP">
                        <strong>{{ $point->orders }}</strong>
                        <span style="height:{{ $point->orders ? max(8, ($point->orders / $maxOrders) * 100) : 2 }}%"></span>
                        <small>{{ $point->day->format('d M') }}</small>
                    </div>
                @endforeach
            </div>
        </section>
        <div class="admin-analytics-side">
            <section class="admin-section"><h2>Top-selling products</h2><ol class="list-unstyled">@forelse($topProducts as $item)<li class="d-flex justify-content-between py-2"><span>{{ $item->product_name }}</span><strong>{{ $item->units }} units</strong></li>@empty<li>No sales data yet.</li>@endforelse</ol></section>
            <section class="admin-section"><h2>Low-stock watch</h2><div class="admin-stock-list">@forelse($lowStockProducts as $product)<a href="{{ route('admin.products.edit',$product) }}"><span>{{ $product->name }}</span><strong>{{ $product->stock }}</strong></a>@empty<p>No low-stock products.</p>@endforelse</div></section>
        </div>
    </div>
@endsection
