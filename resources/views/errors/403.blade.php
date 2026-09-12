@extends('layouts.store')
@section('title', 'Access denied')
@section('content')<section class="error-page section-space"><div class="container"><p class="eyebrow text-green">Access denied</p><h1>This area is not available.</h1><p>Your account does not have permission to view this page. Return to your account or continue shopping.</p><div class="d-flex flex-wrap gap-2"><a class="btn btn-dark" href="{{ route('home') }}">Return home</a>@auth<a class="btn btn-outline-dark" href="{{ route('profile.edit') }}">Your account</a>@endauth</div></div></section>@endsection
