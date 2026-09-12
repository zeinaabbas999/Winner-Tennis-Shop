@extends('layouts.store')
@section('title', 'Page not found')
@section('content')<section class="error-page section-space"><div class="container"><p class="eyebrow text-green">Page not found</p><h1>That point is out.</h1><p>The page or product may have moved, become unavailable or never existed.</p><div class="d-flex flex-wrap gap-2"><a class="btn btn-dark" href="{{ route('products.index') }}">Browse the shop</a><a class="btn btn-outline-dark" href="{{ route('home') }}">Return home</a></div></div></section>@endsection
