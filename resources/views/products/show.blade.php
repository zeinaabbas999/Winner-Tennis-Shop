@extends('layouts.store')
@section('title', $product->name)
@section('content')
<div class="container product-detail"><div class="row g-4 g-lg-5">
    <div class="col-lg-7"><div class="product-image-frame detail-image-frame">@if($product->image)<img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><div class="image-fallback" hidden>WINNER TENNIS</div>@else<div class="image-fallback">WINNER TENNIS</div>@endif</div></div>
    <div class="col-lg-5 product-info">
        <p class="product-brand">{{ $product->brand ?: 'WINNER' }}</p><h1>{{ $product->name }}</h1>
        <x-product-price :product="$product" class="product-detail-price" />
        @if($product->hasDiscount())<span class="discount-label">{{ number_format((float) $product->discount_percent, 0) }}% off</span>@endif
        <p class="product-category">{{ $product->category->name }}</p>
        @if($product->reviews_count)<p class="product-detail-rating">{{ number_format((float) $product->reviews_avg_rating, 1) }} / 5 · {{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</p>@else<p class="product-detail-rating">No reviews yet</p>@endif
        <p class="product-description">{{ $product->description ?: 'Selected for dependable performance on court.' }}</p>
        @if($product->specs)<dl class="spec-grid">@foreach($product->specs as $label => $value)@if($value)<div><dt>{{ str($label)->replace('_', ' ')->title() }}</dt><dd>{{ $value }}</dd></div>@endif @endforeach</dl>@endif
        <div class="purchase-area">
            <p class="stock {{ $product->stock ? 'in-stock' : 'out-stock' }}">{{ $product->stock <= 0 ? 'Out of stock' : ($product->stock <= 3 ? 'Only '.$product->stock.' left' : 'In stock') }}</p>
            @if($product->stock > 0)<form method="POST" action="{{ route('cart.store', $product) }}">@csrf<div class="add-row"><input class="form-control" type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" aria-label="Quantity"><button class="btn btn-dark">Add to cart</button></div></form>@endif
        </div>
        <a class="back-link" href="{{ route('products.index') }}">Back to shop</a>
    </div>
</div></div>

<section class="reviews-section"><div class="container reviews-container">
    <header class="reviews-head"><div><p class="eyebrow text-green">Customer reviews</p><h2>Player feedback</h2></div><div>@if($product->reviews_count)<strong>{{ number_format((float) $product->reviews_avg_rating, 1) }} / 5</strong><span>Based on {{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</span>@else<strong>New to the court</strong><span>No reviews yet</span>@endif</div></header>
    @auth
        @if($userReview)
            <form class="review-form shared-panel" method="POST" action="{{ route('reviews.update', $userReview) }}">@csrf @method('PATCH')<h3>Edit your review</h3>@include('products.partials.review-fields', ['review' => $userReview])<div class="review-form-actions"><button class="btn btn-dark">Update review</button></div></form>
            <form class="review-delete" method="POST" action="{{ route('reviews.destroy', $userReview) }}">@csrf @method('DELETE')<button class="btn btn-link text-danger">Delete your review</button></form>
        @elseif($canReview)
            <form class="review-form shared-panel" method="POST" action="{{ route('reviews.store', $product) }}">@csrf<h3>Share your experience</h3>@include('products.partials.review-fields', ['review' => null])<div class="review-form-actions"><button class="btn btn-dark">Publish review</button></div></form>
        @else
            <p class="review-eligibility">Reviews are available after purchasing this product in a non-cancelled order.</p>
        @endif
    @else
        <p class="review-eligibility"><a href="{{ route('login') }}">Log in</a> to review a product you have purchased.</p>
    @endauth
    <div class="review-list">@forelse($reviews as $review)<article class="review-item"><header><div><strong>{{ $review->user->name }}</strong><span>{{ $review->rating }} / 5</span></div><time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d M Y') }}</time></header>@if($review->comment)<p>{{ $review->comment }}</p>@endif</article>@empty<p class="review-empty">Be the first verified customer to review this product.</p>@endforelse</div>
    @if($reviews->hasPages())<div class="mt-4">{{ $reviews->links() }}</div>@endif
</div></section>
@endsection
