<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', ['coupons' => Coupon::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', ['coupon' => new Coupon]);
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function store(Request $request): RedirectResponse
    {
        Coupon::create($this->data($request));

        return to_route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->data($request, $coupon));

        return to_route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        if ($coupon->usages()->exists()) {
            return back()->with('warning', 'Used coupons cannot be deleted; make it inactive.');
        } $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }

    private function data(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50', Rule::unique('coupons')->ignore($coupon)], 'type' => ['required', Rule::in(['percentage', 'fixed'])], 'value' => ['required', 'numeric', 'min:0.01', 'max:999999.99'], 'minimum_order_amount' => ['required', 'numeric', 'min:0'], 'usage_limit' => ['nullable', 'integer', 'min:1'], 'per_user_limit' => ['nullable', 'integer', 'min:1'], 'starts_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date', 'after:starts_at'], 'is_active' => ['nullable', 'boolean']]);
        if ($data['type'] === 'percentage' && (float) $data['value'] > 100) {
            throw ValidationException::withMessages(['value' => 'Percentage coupons cannot exceed 100%.']);
        }
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
