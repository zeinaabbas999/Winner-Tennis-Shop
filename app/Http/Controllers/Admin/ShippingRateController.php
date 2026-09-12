<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShippingRateController extends Controller
{
    public function index(): View
    {
        return view('admin.shipping.index', ['rates' => ShippingRate::orderBy('governorate')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        ShippingRate::create($this->data($request));

        return back()->with('success', 'Shipping rate added.');
    }

    public function update(Request $request, ShippingRate $shippingRate): RedirectResponse
    {
        $shippingRate->update($this->data($request, $shippingRate));

        return back()->with('success', 'Shipping rate updated.');
    }

    public function destroy(ShippingRate $shippingRate): RedirectResponse
    {
        $shippingRate->delete();

        return back()->with('success', 'Shipping rate removed.');
    }

    private function data(Request $request, ?ShippingRate $rate = null): array
    {
        $data = $request->validate(['governorate' => ['required', 'string', 'max:100', Rule::unique('shipping_rates')->ignore($rate)], 'fee' => ['required', 'numeric', 'min:0'], 'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'], 'is_active' => ['nullable', 'boolean']]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
