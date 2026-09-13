<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Account | WINNER</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="storefront auth-shell">
    <header class="auth-header"><div class="container"><a class="brand" href="{{ route('home') }}">WINNER<span>.</span></a><a href="{{ route('products.index') }}">Back to store</a></div></header>
    <main class="auth-main"><div class="container auth-layout"><aside><p class="eyebrow">WINNER account</p><h2>Ready for<br>the next point.</h2><p>Save your details, check out securely and follow every order from one place.</p></aside><section class="auth-card">{{ $slot }}</section></div></main>
    <footer class="auth-footer"><div class="container">WINNER Tennis · Cairo, Egypt</div></footer>
    <x-logout-dialog />
</body>
</html>
