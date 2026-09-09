@props(['product', 'compact' => false])
<span {{ $attributes->class(['product-price-display', 'product-price-compact' => $compact]) }}>
    @if($product->hasDiscount())
        <span class="original-price">{{ number_format((float) $product->price, 2) }} EGP</span>
        <strong class="sale-price">{{ number_format((float) $product->finalPrice(), 2) }} EGP</strong>
    @else
        <strong>{{ number_format((float) $product->price, 2) }} EGP</strong>
    @endif
</span>
