<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'WINNER premium tennis equipment in Egypt')">
    <meta name="robots" content="{{ request()->routeIs('cart.*', 'checkout.*', 'orders.*', 'guest.orders.*', 'wishlist.*', 'profile.*', 'verification.*') ? 'noindex,nofollow' : 'index,follow' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="WINNER">
    <meta property="og:title" content="@yield('title', 'Tennis Equipment') | WINNER">
    <meta property="og:description" content="@yield('meta_description', 'WINNER premium tennis equipment in Egypt')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <title>@yield('title', 'Tennis Equipment') | WINNER</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="storefront {{ request()->routeIs('home') ? 'storefront-home' : 'storefront-inner' }}">
    <header class="store-header sticky-top">
        <div class="container header-inner">
            <button class="menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainMenu"
                aria-controls="mainMenu" aria-label="Open main menu"><span></span><span></span><span></span></button>
            <a class="brand header-brand" href="{{ route('home') }}">WINNER<span>.</span></a>
            <form class="global-search" method="GET" action="{{ route('products.index') }}" role="search">
                <label class="visually-hidden" for="global-search">Search products</label>
                <input id="global-search" name="search" value="{{ request('search') }}" placeholder="Search gear"
                    maxlength="100">
                <button type="submit" aria-label="Submit product search">Search</button>
            </form>
            <div class="header-actions">
            @guest<a class="header-link account-link" href="{{ route('login') }}">Log in / Sign up</a>@else<a
                class="header-link account-link" href="{{ route('profile.edit') }}">Account</a><a class="header-link" href="{{ route('wishlist.index') }}">Wishlist</a>@endguest
            <a class="header-link cart-link" href="{{ route('cart.index') }}">Cart <span
                    class="cart-count">{{ array_sum(session('cart', [])) }}</span></a>
        </div>
    </div>
</header>
<aside class="offcanvas offcanvas-start winner-menu" tabindex="-1" id="mainMenu" aria-labelledby="mainMenuLabel">
    <div class="offcanvas-header"><a class="brand" id="mainMenuLabel" href="{{ route('home') }}"
            data-bs-dismiss="offcanvas">WINNER<span>.</span></a><button type="button" class="menu-close"
            data-bs-dismiss="offcanvas" aria-label="Close main menu"><span></span><span></span></button></div>
    <div class="offcanvas-body">
        <nav aria-label="Main navigation">
            <button class="menu-primary menu-toggle collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#shopMenu" aria-expanded="false" aria-controls="shopMenu">Shop
                <span></span></button>
            <div class="collapse menu-shop-links" id="shopMenu">
                <a class="{{ request()->routeIs('shop.category') && request()->route('category')?->slug === 'tennis-rackets' ? 'active' : '' }}"
                    href="{{ route('shop.category', 'tennis-rackets') }}">Rackets</a>
                <a class="{{ request()->routeIs('shop.category') && request()->route('category')?->slug === 'tennis-balls' ? 'active' : '' }}"
                    href="{{ route('shop.category', 'tennis-balls') }}">Balls</a>
                <a class="{{ request()->routeIs('shop.category') && request()->route('category')?->slug === 'tennis-bags' ? 'active' : '' }}"
                    href="{{ route('shop.category', 'tennis-bags') }}">Tennis Bags</a>
                <a class="{{ request()->routeIs('shop.category') && request()->route('category')?->slug === 'strings' ? 'active' : '' }}"
                    href="{{ route('shop.category', 'strings') }}">Strings</a>
                <button class="menu-subtoggle collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accessoriesMenu" aria-expanded="false"
                    aria-controls="accessoriesMenu">Accessories <span></span></button>
                <div class="collapse menu-accessory-links" id="accessoriesMenu"><a
                        href="{{ route('shop.category', 'dampeners') }}">Dampeners</a><a
                        href="{{ route('shop.category', 'grips-and-overgrips') }}">Overgrips</a><a
                        href="{{ route('shop.category', 'other-tennis-accessories') }}">Other Accessories</a></div>
            </div>
            <div class="menu-main-links"><a href="{{ route('racket-guide') }}">How to Choose a Racket</a><a
                    href="{{ route('services') }}">Services</a><a href="{{ route('contact') }}">Contact</a><a
                    href="{{ route('policies') }}">Policies</a></div>
        </nav>
        <div class="menu-account-links">@auth<a href="{{ route('profile.edit') }}">Account</a><a
                    href="{{ route('orders.index') }}">My Orders</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                @csrf<button type="submit">Log out</button></form>@else<a href="{{ route('login') }}">Log in</a><a
                href="{{ route('register') }}">Sign up</a>@endauth
        </div>
    </div>
</aside>
@include('partials.cookie-consent')
@include('partials.alerts')
<main>@yield('content')</main>
@php
    $engagementExcluded = request()->routeIs('cart.*', 'checkout.*', 'wishlist.*', 'privacy', 'terms', 'cookies', 'policies', 'contact', 'verification.*');
    $cartReminderCount = array_sum(session('cart', []));
    $wishlistReminderCount = ! $engagementExcluded && auth()->check() ? auth()->user()->wishlist()->count() : 0;
    $showOfferPrompt = ! $engagementExcluded && app(\App\Support\FirstOrderOffers::class)->shouldPrompt(request());
@endphp
@if($showOfferPrompt)
    <div class="modal fade winner-offer-modal" id="firstOrderOffer" tabindex="-1" aria-labelledby="firstOrderOfferTitle" aria-hidden="true" inert data-offer-modal data-spin-url="{{ route('first-order-offer.store') }}" data-dismiss-url="{{ route('first-order-offer.dismiss') }}">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <button class="winner-offer-modal__close" type="button" data-bs-dismiss="modal" aria-label="Close first order offer">×</button>
            <p class="eyebrow">First order offer</p>
            <h2 id="firstOrderOfferTitle">A welcome from WINNER</h2>
            <p>Spin once to unlock an offer for your first order.</p>
            <div class="offer-wheel" aria-hidden="true"><span>5%</span><span>10%</span><span>15%</span><span>100</span><span>200</span><span>Free</span><b>W</b></div>
            <button class="btn btn-light" type="button" data-spin-offer>Spin the wheel</button>
            <p class="first-order-reward__result" data-spin-result aria-live="polite"></p>
        </div></div>
    </div>
@endif
@if(! $engagementExcluded && $cartReminderCount > 0)
    <aside class="store-reminder" hidden data-store-reminder="cart" role="status"><button type="button" aria-label="Dismiss cart reminder" data-dismiss-reminder>×</button><strong>Your cart is waiting</strong><span>{{ $cartReminderCount }} {{ Str::plural('item', $cartReminderCount) }} ready for checkout.</span><a href="{{ route('cart.index') }}">View cart</a></aside>
@elseif(! $engagementExcluded && $wishlistReminderCount > 0)
    <aside class="store-reminder" hidden data-store-reminder="wishlist" role="status"><button type="button" aria-label="Dismiss wishlist reminder" data-dismiss-reminder>×</button><strong>Saved for later</strong><span>{{ $wishlistReminderCount }} {{ Str::plural('item', $wishlistReminderCount) }} in your wishlist.</span><a href="{{ route('wishlist.index') }}">View wishlist</a></aside>
@endif
@if(session('order_placed'))
    <div data-order-celebration="{{ session('order_placed') }}" aria-hidden="true"></div>
@endif
<footer>
    <div class="container footer-grid">
        <div><a class="brand" href="{{ route('home') }}">WINNER<span>.</span></a>
            <p class="mt-3 text-secondary">Equipment for better tennis.</p>
        </div>
        <div><strong>Shop</strong><a href="{{ route('shop.category', 'tennis-rackets') }}">Rackets</a><a
                href="{{ route('shop.category', 'tennis-bags') }}">Tennis Bags</a><a
                href="{{ route('shop.accessories') }}">Accessories</a></div>
        <div><strong>Help</strong><a href="{{ route('racket-guide') }}">How to Choose a Racket</a><a
                href="{{ route('services') }}">Services</a><a href="{{ route('contact') }}">Contact</a><a
                href="{{ route('policies') }}">Shipping and Returns</a></div>
        <div><strong>Legal</strong><a href="{{ route('privacy') }}">Privacy Policy</a><a
                href="{{ route('terms') }}">Terms and Conditions</a><a href="{{ route('cookies') }}">Cookie Policy</a><button
                class="footer-cookie-settings" type="button" data-cookie-settings>Cookie settings</button></div>
        <div><strong>WINNER</strong>
            <p class="text-secondary">Cairo, Egypt<br><a
                    href="mailto:winner.tennis999@gmail.com">winner.tennis999@gmail.com</a></p>
        </div>
    </div>
    <div class="container footer-bottom">© {{ date('Y') }} WINNER. Review our <a
            href="{{ route('policies') }}">shipping, returns and exchange policies</a>.</div>
</footer>
<x-logout-dialog />
</body>

</html>
