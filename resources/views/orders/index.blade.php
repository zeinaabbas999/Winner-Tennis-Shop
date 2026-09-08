@extends('layouts.store')
@section('title', 'My Orders')
@section('content')
<section class="orders-page section-space">
    <div class="container">
        <header class="page-intro orders-intro">
            <p class="eyebrow text-green">Your account</p>
            <h1>My orders</h1>
            <p>Review every WINNER order, its current status and the equipment selected for your game.</p>
        </header>
        <div class="order-list">
            @forelse($orders as $order)
                <article class="order-card">
                    <div class="order-card-main"><span class="order-label">Order</span><h2>{{ $order->order_number }}</h2><time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('d M Y') }}</time></div>
                    <div class="order-card-meta"><div><span>Status</span><strong class="order-status order-status-{{ $order->status }}">{{ ucfirst($order->status) }}</strong></div><div><span>Items</span><strong>{{ $order->items_count }}</strong></div><div><span>Total</span><strong>{{ number_format($order->total, 2) }} EGP</strong></div></div>
                    <a class="order-view" href="{{ route('orders.show', $order) }}">View order</a>
                </article>
            @empty
                <div class="orders-empty"><div><span class="orders-empty-mark">00</span><p class="eyebrow text-green">Your first point starts here</p></div><div><h2>No orders yet</h2><p>Your order history will appear here after checkout. Explore rackets and court essentials selected for every level.</p><a class="btn btn-dark" href="{{ route('shop.category', 'tennis-rackets') }}">Shop rackets</a></div></div>
            @endforelse
        </div>
        @if($orders->hasPages())<div class="orders-pagination">{{ $orders->links() }}</div>@endif
    </div>
</section>
@endsection
