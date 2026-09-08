@extends('layouts.store')
@section('title', 'Your Cart')
@section('content')
<section class="cart-page section-space"><div class="container">
    <header class="page-intro cart-intro"><div><p class="eyebrow text-green">Your selection</p><h1>Cart</h1></div><a href="{{ route('products.index') }}">Continue shopping</a></header>
    @forelse($cart as $item)
        @php($lineTotal = $item['product']->price * $item['quantity'])
        <div class="cart-row"><div>@if($item['product']->image)<img src="{{ asset('storage/'.$item['product']->image) }}" alt="{{ $item['product']->name }}">@else<div class="cart-placeholder">WINNER</div>@endif</div><div><small>{{ $item['product']->brand }}</small><a href="{{ route('products.show',$item['product']) }}">{{ $item['product']->name }}</a><span>{{ number_format($item['product']->price,2) }} EGP each</span></div><form method="POST" action="{{ route('cart.update',$item['product']) }}">@csrf @method('PATCH')<label>Quantity</label><div class="d-flex gap-2"><input class="form-control" type="number" name="quantity" min="1" max="{{ $item['product']->stock }}" value="{{ $item['quantity'] }}"><button class="btn btn-outline-dark">Update</button></div></form><strong>{{ number_format($lineTotal,2) }} EGP</strong><form method="POST" action="{{ route('cart.destroy',$item['product']) }}">@csrf @method('DELETE')<button class="btn btn-link text-danger">Remove</button></form></div>
    @empty
        <div class="cart-empty"><span class="cart-empty-number">00</span><div><h2>Your cart is empty</h2><p>Add a racket or court essential when you are ready.</p><a class="btn btn-dark" href="{{ route('products.index') }}">Shop the collection</a></div></div>
    @endforelse
    @if($cart->isNotEmpty())<div class="cart-summary"><div><span>Subtotal</span><strong>{{ number_format($subtotal,2) }} EGP</strong></div><p>Delivery costs, if applicable, are confirmed at checkout.</p><a class="btn btn-dark w-100" href="{{ route('checkout.create') }}">Proceed to checkout</a></div>@endif
</div></section>
@endsection
