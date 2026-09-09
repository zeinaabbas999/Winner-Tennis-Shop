@extends('layouts.store')

@section('title', 'Policies')

@section('content')
    <header class="info-page-header">
        <div class="container info-page-header__inner">
            <div>
                <p class="eyebrow text-green">Customer care</p>
                <h1>Policies</h1>
            </div>
            <p>Clear guidance for shipping, returns and exchanges when ordering tennis equipment from WINNER.</p>
        </div>
    </header>

    <nav class="info-index" aria-label="Policies sections">
        <div class="container info-index__inner">
            <span>On this page</span>
            <a href="#shipping">Shipping</a>
            <a href="#returns">Returns</a>
            <a href="#exchanges">Exchanges</a>
        </div>
    </nav>

    <div class="info-page-content">
        <div class="container info-sections">
            <section class="info-section" id="shipping">
                <header class="info-section__heading"><span>01</span><div><p class="eyebrow text-green">Delivery</p><h2>Shipping</h2></div></header>
                <div class="info-section__body"><p>Orders are normally prepared within two business days. Delivery estimates vary by destination and courier availability, and we will share order updates when available.</p><p>Weather, public holidays and courier disruptions can cause delays outside WINNER's control. Please check your delivery details carefully before placing an order.</p></div>
            </section>
            <section class="info-section" id="returns">
                <header class="info-section__heading"><span>02</span><div><p class="eyebrow text-green">Eligibility</p><h2>Returns</h2></div></header>
                <div class="info-section__body"><p>Unused products in their original condition and packaging may be eligible for return within 14 days of delivery. Proof of purchase is required.</p><p>Used, damaged or altered equipment may not qualify. Opened grips and other hygiene-sensitive items, and customized or strung rackets, may be excluded unless faulty.</p></div>
            </section>
            <section class="info-section" id="exchanges">
                <header class="info-section__heading"><span>03</span><div><p class="eyebrow text-green">Availability</p><h2>Exchanges</h2></div></header>
                <div class="info-section__body"><p>Eligible products may be exchanged subject to current stock. A replacement model, size or product can be discussed when the original option is unavailable.</p><p>Any price difference will be confirmed before an exchange proceeds. Contact WINNER with your order number and product details to begin an eligibility check.</p></div>
            </section>
        </div>
    </div>
@endsection
