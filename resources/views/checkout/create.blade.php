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
                    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
                    <section class="customer-panel checkout-section" aria-labelledby="contact-details">
                        <p class="checkout-step">Contact</p>
                        <h2 id="contact-details">Customer details</h2>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="customer_name">Full Name</label><input id="customer_name" class="form-control" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required></div>
                            <div class="col-md-6"><label class="form-label" for="customer_email">Email</label><input id="customer_email" class="form-control" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" required></div>
                            <div><label class="form-label" for="phone">Phone Number</label><input id="phone" class="form-control" name="phone" value="{{ old('phone') }}" required></div>
                        </div>
                    </section>
                    <section class="customer-panel checkout-section" aria-labelledby="delivery-details">
                        <p class="checkout-step">Delivery</p>
                        <h2 id="delivery-details">Delivery details</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="governorate">Governorate</label>
                                <input id="governorate" class="form-control" name="governorate" value="{{ old('governorate') }}" list="governorates" autocomplete="address-level1" required><datalist id="governorates">@foreach($shippingRates as $rate)<option value="{{ $rate->governorate }}">@endforeach</datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="city_area">City / Area</label>
                                <input id="city_area" class="form-control" name="city_area" value="{{ old('city_area') }}" autocomplete="address-level2" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="street">Street name</label>
                                <input id="street" class="form-control" name="street" value="{{ old('street') }}" autocomplete="street-address" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="building_number">Building number</label>
                                <input id="building_number" class="form-control" name="building_number" value="{{ old('building_number') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="floor_apartment">Floor / Apartment <span class="optional-label">Optional</span></label>
                                <input id="floor_apartment" class="form-control" name="floor_apartment" value="{{ old('floor_apartment') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="landmark">Landmark <span class="optional-label">Optional</span></label>
                                <input id="landmark" class="form-control" name="landmark" value="{{ old('landmark') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="delivery_notes">Delivery notes <span class="optional-label">Optional</span></label>
                                <textarea id="delivery_notes" class="form-control" rows="3" name="delivery_notes">{{ old('delivery_notes') }}</textarea>
                            </div>
                        </div>
                        <p class="checkout-supporting">Delivery timing and any applicable costs are confirmed with your order.</p>
                    </section>
                    <section class="customer-panel checkout-section checkout-payment" aria-labelledby="payment-details">
                        <p class="checkout-step">Payment</p>
                        <h2 id="payment-details">Payment details</h2>
                        <p>Cash on Delivery is the currently supported payment method.</p>
                        <div class="payment-option" role="status"><span class="payment-option__mark" aria-hidden="true"></span><span><strong>Cash on Delivery</strong><small>Pay when your WINNER order arrives.</small></span></div>
                        <div class="payment-future"><strong>Future payment methods · Coming soon</strong><span>Card via Paymob and mobile wallet options can be added here when a secure gateway is connected.</span></div>
                    </section>
                    @auth
                    <section class="customer-panel checkout-section" aria-labelledby="rewards-details">
                        <p class="checkout-step">Rewards</p><h2 id="rewards-details">Use your points</h2>
                        <p>You have <strong>{{ number_format($availablePoints) }} points</strong>, worth up to {{ number_format($availablePoints * .10, 2) }} EGP.</p>
                        <label class="form-label" for="points_to_redeem">Points to redeem</label>
                        <select class="form-select" id="points_to_redeem" name="points_to_redeem"><option value="0">Do not use points</option>@for($points = 100; $points <= min($availablePoints - ($availablePoints % 100), floor(($subtotal - $couponDiscount) * 10 / 100) * 100); $points += 100)<option value="{{ $points }}" @selected((int) old('points_to_redeem') === $points)>{{ number_format($points) }} points, {{ number_format($points * .10, 2) }} EGP off</option>@endfor</select>
                        <small>Redeem in blocks of 100 points. Shipping is not covered by points.</small>
                    </section>
                    @endauth
                    <button class="btn btn-dark w-100" type="submit">Place order</button>
                </form>
                <aside>
                    <div class="checkout-summary">
                        <p class="checkout-step">Your order</p>
                        <h2>Order summary</h2>
                        @foreach($cart as $item)
                            <article class="checkout-summary-item">
                                <div class="checkout-summary-media">
                                    @if($item['product']->imageUrl())
                                        <img src="{{ $item['product']->imageUrl() }}" alt="{{ $item['product']->name }}">
                                    @else
                                        <span>WINNER</span>
                                    @endif
                                </div>
                                <div><strong>{{ $item['product']->name }}</strong>@if($item['variant'])<small>{{ $item['variant']->name }}</small>@endif<small>Quantity {{ $item['quantity'] }} · {{ number_format((float) $item['unit_price'], 2) }} EGP each</small>@if($item['product']->hasDiscount())<small class="checkout-saving">{{ number_format((float) $item['product']->discount_percent) }}% discount applied</small>@endif</div>
                                <strong>{{ number_format((float) $item['unit_price'] * $item['quantity'], 2) }} EGP</strong>
                            </article>
                        @endforeach
                        @if($coupon)<div class="d-flex justify-content-between"><span>Coupon {{ $coupon->code }}</span><strong>-{{ number_format($couponDiscount, 2) }} EGP</strong></div>@endif
                        <p class="small">Shipping is calculated from the entered governorate.</p>
                        <footer><span>Estimated total before shipping</span><strong>{{ number_format($subtotal - $couponDiscount, 2) }} EGP</strong></footer>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
