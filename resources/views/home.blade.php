@extends('layouts.store')
@section('title', 'Premium Tennis Equipment')
@section('content')
    <section class="hero" style="--hero-image: url('{{ asset('images/home-2.png') }}')">
        <div class="container hero-content">
            <p class="eyebrow">WINNER TENNIS</p>
            <h1>Own the<br>next point.</h1>
            <p class="hero-copy">Rackets, equipment and court essentials selected for players who take every session
                seriously.</p>
            <div class="d-flex flex-wrap gap-2"><a class="btn btn-lime"
                    href="{{ route('shop.category', 'tennis-rackets') }}">Shop rackets</a><a class="btn btn-outline-light"
                    href="{{ route('shop.accessories') }}">Shop accessories</a></div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="section-heading">
                <div>
                    <p class="eyebrow text-green">Shop by category</p>
                    <h2>Built for the court</h2>
                </div><a href="{{ route('products.index') }}">View all products</a>
            </div>
            <div class="category-grid">
                @foreach ([['tennis-rackets', 'Rackets', '01', 'rackets.png'], ['tennis-bags', 'Bags', '02', 'bags.png'], ['accessories', 'Accessories', '03', 'accessories.png'], ['tennis-balls', 'Balls', '04', 'tennis-balls.png'], ['grips-and-overgrips', 'Grips / Overgrips', '05', 'overgrips.png']] as [$slug, $name, $number, $image])
                    <a href="{{ $slug === 'accessories' ? route('shop.accessories') : route('shop.category', $slug) }}"><img
                            src="{{ asset('images/categories/' . $image) }}" alt=""
                            loading="lazy"><span>{{ $number }}</span>
                        <div><strong>{{ $name }}</strong><b>Explore</b></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    <section class="play-styles">
        <div class="container play-styles-inner">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Find your feel</p>
                    <h2>Power, control or spin</h2>
                </div><a href="{{ route('racket-guide') }}">Read the racket guide</a>
            </div>
            <div class="play-style-grid">
                <article><span>01</span>
                    <h3>Power</h3>
                    <p>Look for a forgiving head size and accessible depth when you want more help behind the ball.</p>
                </article>
                <article><span>02</span>
                    <h3>Control</h3>
                    <p>Choose stability and a precise response when placement and confidence through impact lead your game.
                    </p>
                </article>
                <article><span>03</span>
                    <h3>Spin</h3>
                    <p>An open string pattern and fast handling can help shape the ball with heavier rotation.</p>
                </article>
            </div>
        </div>
    </section>
    <section class="section section-white">
        <div class="container">
            <div class="section-heading">
                <div>
                    <p class="eyebrow text-green">Court essentials</p>
                    <h2>Ready for every session</h2>
                </div>
            </div>
            <div class="row g-4 product-grid">
                @forelse($newArrivals as $product)
                <div class="col-6 col-lg-3">@include('partials.product-card')</div>@empty<div class="empty-panel">The WINNER
                        collection is being prepared.</div>
                @endforelse
            </div>
        </div>
    </section>
    <section class="editorial">
        <div class="container">
            <p class="eyebrow">The WINNER standard</p>
            <h2>Good equipment.<br>Clear advice.<br>Better tennis.</h2><a href="{{ route('contact') }}">Connect with
                WINNER</a>
        </div>
    </section>
@endsection
