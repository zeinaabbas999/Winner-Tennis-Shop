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
        $offer = DB::transaction(function () use ($request, $offers) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);

            return $offers->claim($user);
        }, 3);

        return response()->json(['label' => $offer->label()]);
    }
}
