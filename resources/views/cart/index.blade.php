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
                @php($lineTotal = (float) $item['product']->finalPrice() * $item['quantity'])
                <article class="cart-row">
                    <div class="cart-media">
                        @if($item['product']->image)
                            <img src="{{ asset('storage/'.$item['product']->image) }}" alt="{{ $item['product']->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                            <div class="cart-placeholder" hidden>WINNER</div>
                        @else
                            <div class="cart-placeholder">WINNER</div>
                        @endif
                    </div>
                    <div class="cart-product-copy">
                        <small>{{ $item['product']->brand }}</small>
                        <a href="{{ route('products.show', $item['product']) }}">{{ $item['product']->name }}</a>
                        <span>{{ number_format((float) $item['product']->finalPrice(), 2) }} EGP each</span>
                    </div>
                    <form method="POST" action="{{ route('cart.update', $item['product']) }}">
                        @csrf
                        @method('PATCH')
                        <label for="quantity-{{ $item['product']->id }}">Quantity</label>
                        <div class="d-flex gap-2">
                            <input id="quantity-{{ $item['product']->id }}" class="form-control" type="number" name="quantity" min="1" max="{{ $item['product']->stock }}" value="{{ $item['quantity'] }}">
                            <button class="btn btn-outline-dark" type="submit">Update</button>
                        </div>
                    </form>
                    <strong>{{ number_format($lineTotal, 2) }} EGP</strong>
                    <form method="POST" action="{{ route('cart.destroy', $item['product']) }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-link text-danger" type="submit">Remove</button>
                    </form>
                </article>
            @empty
                <x-empty-state title="No items yet" text="Your cart is currently empty." :href="route('products.index')" action="Shop the collection" />
            @endforelse

            @if($cart->isNotEmpty())
                <div class="cart-summary">
                    <div><span>Subtotal</span><strong>{{ number_format($subtotal, 2) }} EGP</strong></div>
                    <p>Delivery costs, if applicable, are confirmed at checkout.</p>
                    <a class="btn btn-dark w-100" href="{{ route('checkout.create') }}">Proceed to checkout</a>
                </div>
            @endif
        </div>
    </section>
@endsection
