@extends('layouts.store')

@section('title', 'Checkout')

@section('content')
    <section class="customer-page checkout-page section-space">
        <div class="container customer-container">
            <header class="customer-page-head customer-page-head-stacked">
                <div><p class="eyebrow text-green">Secure checkout</p><h1>Checkout</h1><p>Confirm your contact, delivery and payment details before placing your order.</p></div>
            </header>
            <div class="checkout-layout">
                <form class="checkout-form" method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <section class="customer-panel checkout-section" aria-labelledby="contact-details">
                        <p class="checkout-step">Contact</p>
                        <h2 id="contact-details">Customer details</h2>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="customer_name">Full Name</label><input id="customer_name" class="form-control" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required></div>
                            <div class="col-md-6"><label class="form-label" for="customer_email">Email</label><input id="customer_email" class="form-control" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}" required></div>
                            <div><label class="form-label" for="phone">Phone Number</label><input id="phone" class="form-control" name="phone" value="{{ old('phone') }}" required></div>
                        </div>
                    </section>
                    <section class="customer-panel checkout-section" aria-labelledby="delivery-details">
                        <p class="checkout-step">Delivery</p>
                        <h2 id="delivery-details">Delivery details</h2>
                        <label class="form-label" for="address">Address</label>
                        <textarea id="address" class="form-control" rows="5" name="address" required>{{ old('address') }}</textarea>
                        <p class="checkout-supporting">Delivery timing and any applicable costs are confirmed with your order.</p>
                    </section>
                    <fieldset class="customer-panel checkout-section checkout-payment">
                        <legend><span class="checkout-step">Payment</span>Payment details</legend>
                        <p>Cash on Delivery is the currently supported payment method.</p>
                        <label class="payment-option"><input type="radio" checked disabled><span><strong>Cash on Delivery</strong><small>Pay when your WINNER order arrives.</small></span></label>
                        <div class="payment-future"><strong>Future payment methods · Coming soon</strong><span>Card via Paymob and mobile wallet options can be added here when a secure gateway is connected.</span></div>
                    </fieldset>
                    <button class="btn btn-dark w-100" type="submit">Place order</button>
                </form>
                <aside>
                    <div class="checkout-summary">
                        <p class="checkout-step">Your order</p>
                        <h2>Order summary</h2>
                        @foreach($cart as $item)
                            <article class="checkout-summary-item">
                                <div class="checkout-summary-media">
                                    @if($item['product']->image)
                                        <img src="{{ asset('storage/'.$item['product']->image) }}" alt="{{ $item['product']->name }}">
                                    @else
                                        <span>WINNER</span>
                                    @endif
                                </div>
                                <div><strong>{{ $item['product']->name }}</strong><small>Quantity {{ $item['quantity'] }} · {{ number_format((float) $item['product']->finalPrice(), 2) }} EGP each</small>@if($item['product']->hasDiscount())<small class="checkout-saving">{{ number_format((float) $item['product']->discount_percent) }}% discount applied</small>@endif</div>
                                <strong>{{ number_format((float) $item['product']->finalPrice() * $item['quantity'], 2) }} EGP</strong>
                            </article>
                        @endforeach
                        <footer><span>Total</span><strong>{{ number_format($subtotal, 2) }} EGP</strong></footer>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
