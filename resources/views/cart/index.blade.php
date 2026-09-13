@extends('layouts.store')

@section('title', 'Your Cart')

@section('content')
    <section class="customer-page section-space">
        <div class="container customer-container">
            <header class="customer-page-head">
                <div>
                    <p class="eyebrow text-green">Your selection</p>
                    <h1>Cart</h1>
                </div>
                <a href="{{ route('products.index') }}">Continue shopping</a>
            </header>

            @forelse($cart as $item)
                @php($lineTotal = (float) $item['unit_price'] * $item['quantity'])
                <article class="cart-row">
                    <div class="cart-media">
                        @if($item['product']->imageUrl())
                            <img src="{{ $item['product']->imageUrl() }}" alt="{{ $item['product']->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                            <div class="cart-placeholder" hidden>WINNER</div>
                        @else
                            <div class="cart-placeholder">WINNER</div>
                        @endif
                    </div>
                    <div class="cart-product-copy">
                        <small>{{ $item['product']->brand }}</small>
                        <a href="{{ route('products.show', $item['product']) }}">{{ $item['product']->name }}</a>
                        @if($item['variant'])<span>{{ $item['variant']->name }}</span>@endif
                        <span>{{ number_format((float) $item['unit_price'], 2) }} EGP each</span>
                    </div>
                    <form method="POST" action="{{ route('cart.update', $item['product']) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="line_key" value="{{ $item['key'] }}">
                        <label for="quantity-{{ $item['product']->id }}">Quantity</label>
                        <div class="d-flex gap-2">
                            <input id="quantity-{{ $item['product']->id }}" class="form-control" type="number" name="quantity" min="1" max="{{ $item['variant']?->stock ?? $item['product']->stock }}" value="{{ $item['quantity'] }}">
                            <button class="btn btn-outline-dark" type="submit">Update</button>
                        </div>
                    </form>
                    <strong>{{ number_format($lineTotal, 2) }} EGP</strong>
                    <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="line_key" value="{{ $item['key'] }}">
                        <button class="btn btn-link text-danger" type="submit">Remove</button>
                    </form>
                </article>
            @empty
                <x-empty-state kind="cart" title="Your cart is empty" text="Add something built for the court." :href="route('products.index')" action="Shop products" />
            @endforelse

            @if($cart->isNotEmpty())
                <div class="cart-summary">
                    <div><span>Subtotal</span><strong>{{ number_format($subtotal, 2) }} EGP</strong></div>
                    @if($coupon)<div class="cart-summary-discount"><span>Coupon {{ $coupon->code }}</span><strong>-{{ number_format($couponDiscount, 2) }} EGP</strong></div>@endif
                    <p>Delivery costs, if applicable, are confirmed at checkout.</p>
                    <form class="mb-3" method="POST" action="{{ route('cart.coupon.store') }}">@csrf<label class="form-label" for="coupon_code">Promo code</label><div class="d-flex gap-2"><input class="form-control" id="coupon_code" name="code" maxlength="50"><button class="btn btn-outline-dark">Apply</button></div></form>
                    @if($coupon)<form class="mb-3" method="POST" action="{{ route('cart.coupon.destroy') }}">@csrf @method('DELETE')<button class="btn btn-link p-0">Remove {{ $coupon->code }}</button></form>@endif
                    <a class="btn btn-dark w-100" href="{{ route('checkout.create') }}">Proceed to checkout</a>
                </div>
            @endif
        </div>
    </section>
@endsection
