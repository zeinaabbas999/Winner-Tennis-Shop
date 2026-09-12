@extends('layouts.store')
@section('title', 'Something went wrong')
@section('content')<section class="error-page section-space"><div class="container"><p class="eyebrow text-green">WINNER support</p><h1>We could not complete that request.</h1><p>Please try again in a moment. If the problem continues, return to the shop or contact WINNER.</p><div class="d-flex flex-wrap gap-2"><a class="btn btn-dark" href="{{ route('products.index') }}">Browse the shop</a><a class="btn btn-outline-dark" href="{{ route('contact') }}">Contact WINNER</a></div></div></section>@endsection
