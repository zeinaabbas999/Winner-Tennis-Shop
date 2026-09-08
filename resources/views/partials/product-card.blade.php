<article class="product-card">
    <a href="{{ route('products.show', $product) }}">
        <div class="product-image-frame">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                <div class="image-fallback" hidden>WINNER TENNIS</div>
            @else
                <div class="image-fallback"><span>{{ $product->category->name ?? 'Tennis' }}</span></div>
            @endif
        </div>
        <div class="product-meta"><small>{{ $product->brand ?: 'WINNER' }}</small><h3>{{ $product->name }}</h3><div><span>{{ $product->category->name }}</span><strong>{{ number_format($product->price, 2) }} EGP</strong></div></div>
    </a>
</article>
