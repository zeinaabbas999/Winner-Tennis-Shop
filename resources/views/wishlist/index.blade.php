@extends('layouts.store')
@section('title', 'Wishlist')
@section('content')
    <section class="customer-page section-space">
        <div class="container customer-container">
            <header class="customer-page-head customer-page-head-stacked">
                <div><p class="eyebrow text-green">Saved for later</p><h1>Wishlist</h1><p>Your favourite WINNER equipment in one place.</p></div>
            </header>
            @forelse ($products as $product)
                @if ($loop->first)<div class="product-grid">@endif
                @include('partials.product-card', ['product' => $product])
                @if ($loop->last)</div>@endif
            @empty
                <x-empty-state title="Your wishlist is empty" text="Save equipment you want to compare or return to later." :href="route('products.index')" action="Shop tennis" />
            @endforelse
            {{ $products->links() }}
        </div>
    </section>
@endsection
