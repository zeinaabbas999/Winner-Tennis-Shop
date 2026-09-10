<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin | WINNER</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="admin-shell">
    <div class="container-fluid">
        <div class="row g-0">
            <aside class="col-md-3 col-xl-2 admin-sidebar"><a class="brand text-white"
                    href="{{ route('admin.dashboard') }}">WINNER<span>.</span></a><span
                    class="admin-nav-label">Administration</span>
                <nav><a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">Overview</a><a
                        class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                        href="{{ route('admin.products.index') }}">Products</a><a
                        class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                        href="{{ route('admin.categories.index') }}">Categories</a><a
                        class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                        href="{{ route('admin.orders.index') }}">Orders</a><a
                        class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"
                        href="{{ route('admin.reviews.index') }}">Reviews</a><a
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        href="{{ route('admin.users.index') }}">Users</a><a
                        class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                        href="{{ route('admin.settings.edit') }}">Settings</a></nav>
                <div class="admin-sidebar-bottom"><a href="{{ route('home') }}">View store</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button
                            class="btn btn-sm btn-outline-light">Logout</button></form>
                </div>
            </aside>
            <main class="col-md-9 col-xl-10 admin-main">@include('partials.alerts')@yield('content')</main>
        </div>
    </div>
</body>

</html>
