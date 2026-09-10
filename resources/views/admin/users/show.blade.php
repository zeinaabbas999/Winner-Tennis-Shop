@extends('layouts.admin')

@section('content')
    <x-admin-page-header eyebrow="Customer" :title="$customer->name" description="Account details and complete order history.">
        <x-slot:action>
            @if($customer->trashed())
                <form method="POST" action="{{ route('admin.users.restore', $customer->id) }}">@csrf @method('PATCH')<button class="btn btn-dark">Restore customer</button></form>
            @else
                <form method="POST" action="{{ route('admin.users.destroy', $customer->id) }}" onsubmit="return confirm('Archive this customer account? Their order history will remain available.')">@csrf @method('DELETE')<button class="btn admin-action-delete">Archive customer</button></form>
            @endif
        </x-slot:action>
    </x-admin-page-header>
    <div class="admin-customer-summary">
        <div><small>Email</small><strong>{{ $customer->email }}</strong></div><div><small>Registered</small><strong>{{ $customer->created_at->format('d M Y') }}</strong></div><div><small>Verification</small><strong>{{ $customer->hasVerifiedEmail() ? 'Verified '.$customer->email_verified_at->format('d M Y') : 'Unverified' }}</strong></div><div><small>Orders</small><strong>{{ $orders->total() }}</strong></div><div><small>Total ordered</small><strong>{{ number_format((float) $totalSpent, 2) }} EGP</strong></div><div><small>Account</small><strong>{{ $customer->trashed() ? 'Archived' : 'Active' }}</strong></div>
    </div>
    <section class="admin-section"><h2>Order history</h2><div class="table-responsive"><table class="table align-middle admin-table"><thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th class="text-end">Action</th></tr></thead><tbody>
        @forelse($orders as $order)
            <tr><td>{{ $order->order_number }}</td><td>{{ $order->created_at->format('d M Y') }}</td><td class="text-capitalize">{{ $order->status }}</td><td>{{ number_format($order->total, 2) }} EGP</td><td><div class="admin-actions"><a class="btn admin-action-edit" href="{{ route('admin.orders.show', $order) }}">View order</a></div></td></tr>
        @empty
            <tr><td colspan="5">This customer has no orders.</td></tr>
        @endforelse
    </tbody></table></div>{{ $orders->links() }}</section>
@endsection
