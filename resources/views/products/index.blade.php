@extends('layouts.store')
@section('title', $catalogueTitle)
@section('content')
<section class="page-head">
    <div class="container">
        <p class="eyebrow text-green">WINNER catalogue</p>
        <h1>{{ $catalogueTitle }}</h1>
        <p>{{ $catalogueDescription }}</p>
    </div>
</section>
<div class="container section pt-0">
    <form class="simple-filters" method="GET">
        <input type="hidden" name="search" value="{{ request('search') }}">
        @if($brands->isNotEmpty())
            <div><label>Brand</label><select class="form-select" name="brand"><option value="">All brands</option>@foreach($brands as $brand)<option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>@endforeach</select></div>
        @endif
        <div><label>Sort</label><select class="form-select" name="sort"><option value="">Recommended</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option></select></div>
        <button class="btn btn-dark">Apply</button>
    </form>
    <div class="results-row">
        <span>{{ $products->total() }} products @if(request('search')) for “{{ request('search') }}” @endif</span>
        @if(request()->query())<a href="{{ $clearUrl }}">Clear filters</a>@endif
    </div>
    <div class="row g-4 product-grid">
        @forelse($products as $product)
            <div class="col-6 col-lg-3">@include('partials.product-card')</div>
        @empty
            <div class="empty-panel"><h2>No products found</h2><p>Try a broader search or clear the filters.</p></div>
        @endforelse
    </div>
    <div class="mt-5">{{ $products->links() }}</div>
</div>
@endsection
