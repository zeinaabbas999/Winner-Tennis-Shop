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
            @php($stages = ['pending', 'confirmed', 'processing', 'shipped', 'delivered']) @php($timelineStatus = $order->status === 'completed' ? 'delivered' : $order->status)
            <ol class="order-timeline" aria-label="Order progress">
                @foreach($stages as $stage)<li class="{{ array_search($stage, $stages, true) <= array_search($timelineStatus, $stages, true) && $order->status !== 'cancelled' ? 'complete' : '' }}"><span></span>{{ ucfirst($stage) }}</li>@endforeach
                @if($order->status === 'cancelled')<li class="cancelled"><span></span>Cancelled</li>@endif
            </ol>

            <div class="order-detail-layout">
                <section class="customer-panel order-detail-products" aria-labelledby="ordered-products">
                    <h2 id="ordered-products">Products</h2>
                    <div class="order-product-list">
                        @foreach($order->items as $item)
                            <article class="order-product-row">
                                <div class="order-product-media">
                                    @if($item->product?->imageUrl())
                                        <img src="{{ $item->product->imageUrl() }}" alt="{{ $item->product_name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
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
                            @if((float)$order->coupon_discount>0)<div><dt>Coupon {{ $order->coupon_code }}</dt><dd>-{{ number_format((float)$order->coupon_discount,2) }} EGP</dd></div>@endif
                            @if($order->first_order_offer_label)<div><dt>{{ $order->first_order_offer_label }}</dt><dd>@if((float)$order->first_order_discount>0)-{{ number_format((float)$order->first_order_discount,2) }} EGP@else Applied @endif</dd></div>@endif
                            @if($order->points_redeemed > 0)<div><dt>Rewards ({{ number_format($order->points_redeemed) }} points)</dt><dd>-{{ number_format((float)$order->points_discount,2) }} EGP</dd></div>@endif
                            <div><dt>Shipping</dt><dd>{{ number_format((float)$order->shipping_fee,2) }} EGP</dd></div>
                            <div class="order-detail-total"><dt>Total</dt><dd>{{ number_format((float) $order->total, 2) }} EGP</dd></div>
                        </dl>
                        @if($order->points_earned > 0)<p class="order-reward-note">{{ number_format($order->points_earned) }} points earned from this order.</p>@endif
                    </section>
                    <section class="customer-panel order-detail-information">
                        <h2>Shipping information</h2>
                        <p>{{ $order->customer_name }}<br><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a><br><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></p>
                        <p class="text-break formatted-address">{{ $order->address }}</p>
                        <h3>Payment details</h3>
                        <p>Cash on delivery</p>
                    </section>
                </aside>
            </div>
            @auth
                @if($order->user_id===auth()->id() && $order->canRequestCancellation())
                    <section class="customer-panel order-request-panel customer-request-form"><p class="eyebrow text-green">Order support</p><h2>Request cancellation</h2><p class="customer-form-intro">Requests are reviewed before an order is cancelled.</p><form class="cancellation-form" method="POST" action="{{ route('orders.cancellation.store', $order) }}">@csrf<label class="form-label" for="cancellation-reason">Reason</label><textarea class="form-control" id="cancellation-reason" name="reason" rows="3" maxlength="1000" required></textarea>@error('reason')<div class="text-danger small">{{ $message }}</div>@enderror<button class="btn btn-dark">Send request</button></form></section>
                @elseif($order->cancellation_status)
                    <p class="order-request-status">Cancellation request: <strong>{{ ucfirst($order->cancellation_status) }}</strong></p>
                @endif
                @if($order->user_id===auth()->id() && in_array($order->status,['delivered','completed'],true))
                    <section class="customer-panel order-request-panel customer-request-form"><p class="eyebrow text-green">Aftercare</p><h2>Return or exchange</h2><p class="customer-form-intro">Choose one delivered item. Requests are reviewed before any next step is confirmed.</p><form class="return-exchange-form" method="POST" action="{{ route('orders.returns.store',$order) }}">@csrf<div class="row g-3"><div class="col-md-4"><label class="form-label" for="request-type">Type</label><select class="form-select" id="request-type" name="type" required><option value="return">Return</option><option value="exchange">Exchange</option></select></div><div class="col-md-8"><label class="form-label" for="request-item">Item</label><select class="form-select" id="request-item" name="order_item_id" required>@foreach($order->items as $item)<option value="{{ $item->id }}">{{ $item->product_name }}@if($item->variant_name) · {{ $item->variant_name }}@endif</option>@endforeach</select></div><div class="col-12"><label class="form-label" for="return-reason">Reason</label><textarea class="form-control" id="return-reason" name="reason" rows="3" maxlength="1500" required></textarea></div></div><button class="btn btn-dark">Submit request</button></form>@if($order->returnExchangeRequests->isNotEmpty())<div class="order-request-history"><h3>Request history</h3>@foreach($order->returnExchangeRequests as $entry)<p><strong>{{ ucfirst($entry->type) }} · {{ ucfirst($entry->status) }}</strong><br>{{ $entry->orderItem?->product_name }}@if($entry->admin_note)<br>{{ $entry->admin_note }}@endif</p>@endforeach</div>@endif</section>
                @endif
            @endauth
        </div>
    </section>
@endsection
