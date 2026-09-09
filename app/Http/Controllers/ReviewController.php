<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Product $product): RedirectResponse
    {
        $product->reviews()->create($request->validated() + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Thank you. Your review has been published.');
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($request->validated());

        return back()->with('success', 'Your review has been updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);
        $review->delete();

        return back()->with('success', 'Review removed.');
    }
}
