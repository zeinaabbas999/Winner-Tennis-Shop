@extends('layouts.store')

@section('title', $order->order_number)

@section('content')
    <section class="customer-page section-space order-detail-page">
        <div class="container customer-container">
            <header class="customer-page-head order-detail-head">
                <div>
                    <p class="eyebrow text-green">Order details</p>
                    <h1>{{ $order->order_number }}</h1>
                </div>
                <div class="order-detail-head__meta">
                    <strong class="order-status order-status-{{ $order->status }}">{{ ucfirst($order->status) }}</strong>
                    <time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('d M Y') }}</time>
                </div>
            </header>

            <div class="order-detail-layout">
                <section class="customer-panel order-detail-products" aria-labelledby="ordered-products">
                    <h2 id="ordered-products">Products</h2>
                    <div class="order-product-list">
                        @foreach($order->items as $item)
                            <article class="order-product-row">
                                <div class="order-product-media">
                                    @if($item->product?->image)
                                        <img src="{{ asset('storage/'.$item->product->image) }}" alt="{{ $item->product_name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                                        <div class="order-product-fallback" hidden>WINNER</div>
                                    @else
                                        <div class="order-product-fallback">WINNER</div>
                                    @endif
                                </div>
                                <div class="order-product-copy">
                                    <h3>{{ $item->product_name }}</h3>
                                    <p>{{ $item->quantity }} × {{ number_format((float) $item->price, 2) }} EGP</p>
                                </div>
                                <strong>{{ number_format((float) $item->subtotal, 2) }} EGP</strong>
                            </article>
                        @endforeach
                    </div>
                </section>

                <aside class="order-detail-aside">
                    <section class="customer-panel order-detail-summary">
                        <h2>Order summary</h2>
                        <dl>
                            <div><dt>Subtotal</dt><dd>{{ number_format((float) $order->subtotal, 2) }} EGP</dd></div>
                            <div><dt>Delivery</dt><dd>Included</dd></div>
                            <div class="order-detail-total"><dt>Total</dt><dd>{{ number_format((float) $order->total, 2) }} EGP</dd></div>
                        </dl>
                    </section>
                    <section class="customer-panel order-detail-information">
                        <h2>Shipping information</h2>
                        <p>{{ $order->customer_name }}<br>{{ $order->customer_email }}<br>{{ $order->phone }}</p>
                        <p class="text-break">{{ $order->address }}</p>
                        <h3>Payment details</h3>
                        <p>Cash on delivery</p>
                    </section>
                </aside>
            </div>
        </div>
    </section>
@endsection
