@extends('layouts.store')
@section('title', 'Page expired')
@section('content')<section class="error-page section-space"><div class="container"><p class="eyebrow text-green">Session expired</p><h1>Let us try that again.</h1><p>Your secure session expired before the request was completed. No order was placed by this request.</p><a class="btn btn-dark" href="{{ route('cart.index') }}">Return to cart</a></div></section>@endsection
