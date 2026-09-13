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
                        <option value="{{ $category->id }}" data-category-slug="{{ $category->slug }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="form-label">Description</label>
                <textarea class="form-control" rows="4" name="description">{{ old('description', $product->description) }}</textarea>
            </div>
            @php($specGroups = [
                'tennis-rackets' => ['head_size'=>'Head Size','weight'=>'Unstrung Weight','strung_weight'=>'Strung Weight','balance'=>'Balance','length'=>'Length','string_pattern'=>'String Pattern','beam_width'=>'Beam Width','composition'=>'Composition / Material','grip_size'=>'Grip Size Information','skill_level'=>'Recommended Player Level','play_style'=>'Play Style / Character','stiffness'=>'Stiffness'],
                'tennis-balls' => ['ball_type'=>'Ball Type','balls_per_can'=>'Balls per Can','court_surface'=>'Court Surface','pressure'=>'Pressurized / Pressureless','felt_type'=>'Felt Type','competition_level'=>'Competition Level'],
                'tennis-bags' => ['racquet_capacity'=>'Racquet Capacity','bag_type'=>'Bag Type','compartments'=>'Number of Compartments','shoe_compartment'=>'Shoe Compartment','thermal_protection'=>'Thermal Protection','dimensions'=>'Dimensions','material'=>'Material','straps'=>'Shoulder / Backpack Straps'],
                'strings' => ['string_type'=>'String Type','gauge'=>'Gauge','pack_length'=>'Length','construction'=>'Construction','material'=>'Material','playing_character'=>'Playing Character'],
                'grips-and-overgrips' => ['grip_type'=>'Grip Type','thickness'=>'Thickness','pack_length'=>'Length','pack_size'=>'Pack Size','feel'=>'Tacky / Dry Feel','absorbency'=>'Absorbency','material'=>'Material','color'=>'Color'],
                'dampeners' => ['dampener_type'=>'Type','material'=>'Material','pack_size'=>'Pack Size','shape_design'=>'Shape / Design'],
                'other-tennis-accessories' => ['material'=>'Material','dimensions'=>'Dimensions','pack_size'=>'Pack Size','color'=>'Color'],
            ])
            @foreach($specGroups as $slug => $fields)<fieldset class="col-12 product-spec-group" data-spec-category="{{ $slug }}" hidden><legend>{{ collect($categories)->firstWhere('slug', $slug)?->name ?? 'Product' }} specifications <small>Optional</small></legend><div class="row g-3">@foreach($fields as $key => $label)<div class="col-md-4"><label class="form-label">{{ $label }}</label><input class="form-control" name="specs[{{ $key }}]" value="{{ old('specs.'.$key, $product->specs[$key] ?? '') }}"></div>@endforeach</div></fieldset>@endforeach
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
            <div class="col-md-5"><label class="form-label" for="new_badge_mode">New badge</label><select class="form-select" id="new_badge_mode" name="new_badge_mode" required><option value="auto" @selected(old('new_badge_mode', $product->new_badge_mode ?? 'auto') === 'auto')>Automatic, first 7 days</option><option value="force_on" @selected(old('new_badge_mode', $product->new_badge_mode) === 'force_on')>Always show</option><option value="force_off" @selected(old('new_badge_mode', $product->new_badge_mode) === 'force_off')>Never show</option></select></div>
            <div><button class="btn btn-dark">Save product</button><a class="btn btn-link text-dark"
                    href="{{ route('admin.products.index') }}">Cancel</a></div>
        </div>
    </form>
@endsection
