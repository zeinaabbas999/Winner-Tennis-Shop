@extends('layouts.admin')
@section('content')
<x-admin-page-header eyebrow="Sales" :title="$coupon->exists ? 'Edit coupon' : 'Add coupon'" description="Configure the offer, order minimum and maximum discount." />
<form class="admin-product-form admin-form-panel" method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
    @csrf @if($coupon->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="coupon-code">Code</label><input class="form-control" id="coupon-code" name="code" value="{{ old('code', $coupon->code) }}" required></div>
        <div class="col-md-3"><label class="form-label" for="coupon-type">Type</label><select class="form-select" id="coupon-type" name="type"><option value="percentage">Percentage</option><option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed EGP</option></select></div>
        <div class="col-md-3"><label class="form-label" for="coupon-value">Value</label><input class="form-control" id="coupon-value" type="number" step=".01" min=".01" name="value" value="{{ old('value', $coupon->value) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="coupon-minimum">Minimum Order Amount</label><input class="form-control" id="coupon-minimum" type="number" step=".01" min="0" name="minimum_order_amount" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount ?? 0) }}" required></div>
        <div class="col-md-6"><label class="form-label" for="coupon-limit">Limit</label><input class="form-control" id="coupon-limit" type="number" step=".01" min=".01" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}"><small class="form-text">Maximum discount amount in EGP for one order.</small></div>
        <div class="col-md-6"><label class="form-label" for="coupon-start">Starts At</label><input class="form-control" id="coupon-start" type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"></div>
        <div class="col-md-6"><label class="form-label" for="coupon-end">Expires At</label><input class="form-control" id="coupon-end" type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"></div>
        <div><input type="hidden" name="is_active" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->exists ? $coupon->is_active : true))> Active</label></div>
        <div class="admin-form-actions"><button class="btn btn-dark">Save coupon</button><a class="btn btn-outline-dark" href="{{ route('admin.coupons.index') }}">Cancel</a></div>
    </div>
</form>
@endsection
