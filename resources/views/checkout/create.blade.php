@extends('layouts.store')

@section('title', 'Checkout')

@section('content')
    <section class="customer-page checkout-page section-space">
        <div class="container customer-container">
            <header class="customer-page-head customer-page-head-stacked">
                <div>
                    <p class="eyebrow text-green">Secure checkout</p>
                    <h1>Checkout</h1>
                    <p>Confirm your delivery details and review the current product prices before placing your order.</p>
                </div>
            </header>

            <div class="checkout-layout">
                <form class="checkout-form" method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <section class="customer-panel checkout-section" aria-labelledby="delivery-details">
                        <h2 id="delivery-details">Delivery details</h2>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="customer_name">Full Name</label><input id="customer_name" class="form-control" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required></div>
                            <div class="col-md-6"><label class="form-label" for="customer_email">Email</label><input id="customer_email" class="form-control" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}" required></div>
                            <div><label class="form-label" for="phone">Phone Number</label><input id="phone" class="form-control" name="phone" value="{{ old('phone') }}" required></div>
                            <div><label class="form-label" for="address">Address</label><textarea id="address" class="form-control" rows="5" name="address" required>{{ old('address') }}</textarea></div>
                        </div>
                    </section>

                    <fieldset class="customer-panel checkout-section checkout-payment">
                        <legend>Payment details</legend>
                        <p>Choose from the payment methods currently available at WINNER.</p>
                        <label class="payment-option"><input type="radio" checked disabled><span><strong>Cash on Delivery</strong><small>Pay when your order arrives.</small></span></label>
                        <label class="payment-option payment-option--disabled"><input type="radio" disabled><span><strong>Card / Online Payment</strong><small>Coming soon</small></span></label>
                    </fieldset>

                    <button class="btn btn-dark w-100" type="submit">Place order</button>
                </form>

                <aside>
                    <div class="checkout-summary">
                        <h2>Order summary</h2>
                        @foreach($cart as $item)
                            <div><span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span><strong>{{ number_format((float) $item['product']->finalPrice() * $item['quantity'], 2) }} EGP</strong><small>{{ number_format((float) $item['product']->finalPrice(), 2) }} EGP each</small></div>
                        @endforeach
                        <footer><span>Total</span><strong>{{ number_format($subtotal, 2) }} EGP</strong></footer>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
