<article class="product-card">
    @auth
        @php($wishlisted = auth()->user()->wishlistProductIds()->contains($product->id))
        <form class="wishlist-toggle" method="POST" action="{{ $wishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}">
            @csrf
            @if ($wishlisted) @method('DELETE') @endif
            <button type="submit" aria-label="{{ $wishlisted ? 'Remove from' : 'Add to' }} wishlist" aria-pressed="{{ $wishlisted ? 'true' : 'false' }}">{{ $wishlisted ? '♥' : '♡' }}</button>
        </form>
    @else
        <a class="wishlist-toggle" href="{{ route('login') }}" aria-label="Log in to add this product to your wishlist">♡</a>
    @endauth
    <a href="{{ route('products.show', $product) }}">
        <div class="product-image-frame">
            @php($badges = array_slice($product->badges(), 0, 2))
            @if ($badges || $product->effectiveStock() <= 0)
                <div class="product-card-badges" aria-label="Product status">
                    @foreach ($badges as $badge)<span class="discount-badge">{{ $badge }}</span>@endforeach
                    @if ($product->effectiveStock() <= 0)<span class="stock-badge">Out of stock</span>@endif
                </div>
            @endif
            @if ($product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                <div class="image-fallback" hidden>WINNER TENNIS</div>
            @else
                <div class="image-fallback"><span>{{ $product->category->name ?? 'Tennis' }}</span></div>
            @endif
        </div>
        <div class="product-meta">
            <small>{{ $product->brand ?: 'WINNER' }}</small>
            <h3>{{ $product->name }}</h3>
            @if (($product->reviews_count ?? 0) > 0)<p class="product-rating">{{ number_format((float) $product->reviews_avg_rating, 1) }} / 5 · {{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</p>@endif
            <div><span>{{ $product->category->name }}</span><x-product-price :product="$product" compact /></div>
        </div>
    </a>
</article>
