{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach([route('home'), route('products.index'), route('racket-guide'), route('services'), route('contact'), route('policies'), route('privacy'), route('terms'), route('cookies')] as $url)
        <url><loc>{{ $url }}</loc></url>
    @endforeach
    @foreach($categories as $category)
        <url><loc>{{ route('shop.category', $category) }}</loc></url>
    @endforeach
    @foreach($products as $product)
        <url><loc>{{ route('products.show', $product) }}</loc><lastmod>{{ $product->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
</urlset>
