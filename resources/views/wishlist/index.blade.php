@extends('layouts.store')
@section('title', 'Wishlist')
@section('content')
    <section class="customer-page section-space">
        <div class="container customer-container">
            <header class="customer-page-head customer-page-head-stacked">
                <div><p class="eyebrow text-green">Saved for later</p><h1>Wishlist</h1><p>Your favourite WINNER equipment in one place.</p></div>
            </header>
            @forelse ($products as $product)
                @if ($loop->first)<div class="row g-4 product-grid">@endif
                <div class="col-6 col-lg-3">@include('partials.product-card', ['product' => $product])</div>
                @if ($loop->last)</div>@endif
            @empty
                <x-empty-state kind="wishlist" title="Nothing saved yet" text="Save the gear you want to come back to." :href="route('products.index')" action="Explore products" />
            @endforelse
            {{ $products->links() }}
        </div>
    </section>
@endsection
