<article class="product-card">
    <a href="{{ route('products.show', $product) }}">
        <div class="product-image-frame">
            @if($product->hasDiscount() || $product->stock <= 0)
                <div class="product-card-badges" aria-label="Product status">
                    @if($product->hasDiscount())
                        <span class="discount-badge">{{ number_format((float) $product->discount_percent) }}% off</span>
                    @endif
                    @if($product->stock <= 0)
                        <span class="stock-badge">Out of stock</span>
                    @endif
                </div>
            @endif
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                <div class="image-fallback" hidden>WINNER TENNIS</div>
            @else
                <div class="image-fallback"><span>{{ $product->category->name ?? 'Tennis' }}</span></div>
            @endif
        </div>
        <div class="product-meta"><small>{{ $product->brand ?: 'WINNER' }}</small><h3>{{ $product->name }}</h3>@if(($product->reviews_count ?? 0) > 0)<p class="product-rating">{{ number_format((float) $product->reviews_avg_rating, 1) }} / 5 · {{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</p>@endif<div><span>{{ $product->category->name }}</span><x-product-price :product="$product" compact /></div></div>
    </a>
</article>
