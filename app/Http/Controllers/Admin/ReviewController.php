<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', [
            'reviews' => Review::with(['product', 'user'])->latest()->paginate(20),
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);
        $review->delete();

        return back()->with('success', 'Review removed.');
    }
}
