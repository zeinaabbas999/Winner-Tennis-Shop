@extends('layouts.admin')

@section('content')
    <x-admin-page-header eyebrow="Customers" title="Users" description="Manage registered WINNER customers and account access." />
    <form class="admin-user-filters" method="GET">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name or email">
        <select class="form-select" name="verification"><option value="">All verification states</option><option value="verified" @selected(request('verification') === 'verified')>Verified</option><option value="unverified" @selected(request('verification') === 'unverified')>Unverified</option></select>
        <select class="form-select" name="status"><option value="">All account states</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="archived" @selected(request('status') === 'archived')>Archived</option></select>
        <button class="btn btn-dark" type="submit">Filter</button>
    </form>
    <div class="table-responsive"><table class="table align-middle admin-table"><thead><tr><th>Customer</th><th>Verification</th><th>Joined</th><th>Orders</th><th>Total ordered</th><th>Status</th><th class="text-end">Action</th></tr></thead><tbody>
        @forelse($users as $customer)
            <tr><td><strong>{{ $customer->name }}</strong><br><small>{{ $customer->email }}</small></td><td>{{ $customer->hasVerifiedEmail() ? 'Verified' : 'Unverified' }}</td><td>{{ $customer->created_at->format('d M Y') }}</td><td>{{ $customer->orders_count }}</td><td>{{ number_format((float) ($customer->orders_sum_total ?? 0), 2) }} EGP</td><td>{{ $customer->trashed() ? 'Archived' : 'Active' }}</td><td><div class="admin-actions"><a class="btn admin-action-edit" href="{{ route('admin.users.show', $customer->id) }}">View</a></div></td></tr>
        @empty
            <tr><td colspan="7">No customers found.</td></tr>
        @endforelse
    </tbody></table></div>
    {{ $users->links() }}
@endsection
