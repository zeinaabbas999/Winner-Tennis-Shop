@extends('layouts.admin')
@section('content')
    <x-admin-page-header eyebrow="Store feedback" title="Reviews"
        description="Review customer feedback and remove content when moderation is required." />
    <div class="table-responsive">
        <table class="table align-middle admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td>{{ $review->product->name }}</td>
                        <td>{{ $review->user->name }}</td>
                        <td>{{ $review->rating }} / 5</td>
                        <td class="admin-review-comment">{{ $review->comment ?: 'No comment' }}</td>
                        <td>{{ $review->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="admin-actions">
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                                    onsubmit="return confirm('Remove this review?')">@csrf @method('DELETE')<button
                                        class="btn admin-action-delete">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                @empty<tr>
                        <td colspan="6">No reviews yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $reviews->links() }}
@endsection
