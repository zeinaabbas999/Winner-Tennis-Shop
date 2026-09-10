<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="WINNER premium tennis equipment in Egypt">
    <title>@yield('title', 'Tennis Equipment') | WINNER</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="storefront">
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
                class="header-link account-link" href="{{ route('profile.edit') }}">Account</a>@endguest
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
@include('partials.alerts')
<main>@yield('content')</main>
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
                href="{{ route('policies') }}">Policies</a></div>
        <div><strong>WINNER</strong>
            <p class="text-secondary">Cairo, Egypt<br><a
                    href="mailto:winner.tennis@gmail.com">winner.tennis@gmail.com</a></p>
        </div>
    </div>
    <div class="container footer-bottom">© {{ date('Y') }} WINNER. Review our <a
            href="{{ route('policies') }}">shipping, returns and exchange policies</a>.</div>
</footer>
</body>

</html>
