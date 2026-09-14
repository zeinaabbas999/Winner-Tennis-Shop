<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\FirstOrderOffers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FirstOrderOfferController extends Controller
{
    public function store(Request $request, FirstOrderOffers $offers): JsonResponse
    {
        if ($request->session()->has('coupon_code')) {
            throw ValidationException::withMessages(['offer' => 'Remove the coupon before claiming a first-order offer.']);
        }
        $offer = $request->user()
            ? DB::transaction(function () use ($request, $offers) {
                User::lockForUpdate()->findOrFail($request->user()->id);

                return $offers->claimForRequest($request);
            }, 3)
            : $offers->claimForRequest($request);

        return response()->json($offer);
    }

    public function dismiss(Request $request): JsonResponse
    {
        $request->session()->put('first_order_offer_prompt_dismissed', true);

        return response()->json(['dismissed' => true]);
    }
}
