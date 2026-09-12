<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()->wishlist()
            ->with('category')->withCount('reviews')->withAvg('reviews', 'rating')
            ->where('is_active', true)->latest('wishlists.created_at')->paginate(12);

        return view('wishlist.index', compact('products'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $request->user()->wishlist()->syncWithoutDetaching([$product->id]);

        return back()->with('success', 'Added to your wishlist.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlist()->detach($product->id);

        return back()->with('success', 'Removed from your wishlist.');
    }
}
