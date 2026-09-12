@extends('layouts.admin')
@section('content')
    <x-admin-page-header eyebrow="Catalogue" :title="$product->exists ? 'Edit product' : 'Add product'"
        description="Maintain product details, price, discount, stock and storefront presentation." />
    <form class="admin-product-form" method="POST" enctype="multipart/form-data"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif
        <div class="row g-3">
            <div class="col-md-5"><label class="form-label">Name</label><input class="form-control" name="name"
                    value="{{ old('name', $product->name) }}" required></div>
            <div class="col-md-3"><label class="form-label">Brand</label><input class="form-control" name="brand"
                    value="{{ old('brand', $product->brand) }}"></div>
            <div class="col-md-4"><label class="form-label">Category</label><select class="form-select" name="category_id"
                    required>
                    <option value="">Choose category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="form-label">Description</label>
                <textarea class="form-control" rows="4" name="description">{{ old('description', $product->description) }}</textarea>
            </div>
            @foreach (['weight' => 'Weight', 'head_size' => 'Head size', 'balance' => 'Balance', 'string_pattern' => 'String pattern', 'skill_level' => 'Skill level', 'play_style' => 'Playing style'] as $key => $label)
                <div class="col-md-4"><label class="form-label">{{ $label }}</label><input class="form-control"
                        name="specs[{{ $key }}]" value="{{ old('specs.' . $key, $product->specs[$key] ?? '') }}">
                </div>
            @endforeach
            <div class="col-md-3"><label class="form-label">Price (EGP)</label><input class="form-control" type="number"
                    min=".01" step=".01" name="price" value="{{ old('price', $product->price) }}" required></div>
            <div class="col-md-3"><label class="form-label">Discount %</label><input class="form-control" type="number"
                    min="0" max="100" step=".01" name="discount_percent"
                    value="{{ old('discount_percent', $product->discount_percent ?? 0) }}"></div>
            <div class="col-md-3"><label class="form-label">Stock</label><input class="form-control" type="number"
                    min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required></div>
            <div class="col-md-3"><label class="form-label">Image</label><input class="form-control" type="file"
                    name="image" accept="image/jpeg,image/png,image/webp">@if($product->imageUrl())<img class="mt-2" src="{{ $product->imageUrl() }}" alt="Current {{ $product->name }} image" style="width:100px;height:100px;object-fit:contain">@endif</div>
            <div>
                <div class="form-check"><input type="hidden" name="is_active" value="0"><input
                        class="form-check-input" type="checkbox" name="is_active" value="1"
                        @checked(old('is_active', $product->exists ? $product->is_active : true))><label class="form-check-label">Active in storefront</label></div>
            </div>
            <div><div class="form-check"><input type="hidden" name="is_featured" value="0"><input class="form-check-input" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))><label class="form-check-label">Featured on homepage</label></div></div>
            <div><button class="btn btn-dark">Save product</button><a class="btn btn-link text-dark"
                    href="{{ route('admin.products.index') }}">Cancel</a></div>
        </div>
    </form>
    @if($product->exists)
        <section class="admin-order-panel mt-5"><h2>Product variants</h2><p>Optional choices such as grip size, gauge, pack size or capacity. Products without variants continue to use product stock.</p>
            @foreach($product->variants as $variant)<form class="row g-2 align-items-end mb-3" method="POST" action="{{ route('admin.products.variants.update',[$product,$variant]) }}">@csrf @method('PATCH')<div class="col-md-2"><label class="form-label">SKU</label><input class="form-control" name="sku" value="{{ $variant->sku }}" required></div><div class="col-md-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $variant->name }}" required></div><div class="col-md-3"><label class="form-label">Attributes JSON</label><input class="form-control" name="attributes" value="{{ json_encode($variant->attributes) }}"></div><div class="col-md-2"><label class="form-label">Price</label><input class="form-control" name="price" type="number" step=".01" value="{{ $variant->price }}"></div><div class="col-md-1"><label class="form-label">Stock</label><input class="form-control" name="stock" type="number" min="0" value="{{ $variant->stock }}"></div><div class="col-md-2"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked($variant->is_active)> Active</label><button class="btn btn-sm btn-dark">Update</button></div></form>@endforeach
            <form class="row g-2 align-items-end" method="POST" action="{{ route('admin.products.variants.store',$product) }}">@csrf<div class="col-md-2"><label class="form-label">SKU</label><input class="form-control" name="sku" required></div><div class="col-md-2"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="col-md-3"><label class="form-label">Attributes JSON</label><input class="form-control" name="attributes" placeholder='{"grip_size":"3"}'></div><div class="col-md-2"><label class="form-label">Price</label><input class="form-control" name="price" type="number" step=".01"></div><div class="col-md-1"><label class="form-label">Stock</label><input class="form-control" name="stock" type="number" min="0" value="0" required></div><div class="col-md-2"><input type="hidden" name="is_active" value="1"><button class="btn btn-outline-dark">Add variant</button></div></form>
        </section>
    @endif
@endsection
