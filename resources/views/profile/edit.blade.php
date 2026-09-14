@extends('layouts.store')
@section('title', 'Account')
@section('content')
<section class="customer-page section-space"><div class="container customer-container">
    <header class="customer-page-head"><div><p class="eyebrow text-green">Your WINNER account</p><h1>Account</h1></div></header>
    <div class="account-layout">
        <aside class="customer-panel account-summary"><p class="panel-eyebrow">Account details</p><h2 class="panel-title">{{ $user->name }}</h2><a class="account-email" href="mailto:{{ $user->email }}">{{ $user->email }}</a><div class="account-actions"><a class="btn btn-dark" href="{{ route('orders.index') }}">My Orders</a>@if($user->isAdmin())<a class="btn btn-dark" href="{{ route('admin.dashboard') }}">Admin Panel</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-dark" type="submit">Log out</button></form></div></aside>
        <section class="customer-panel account-form"><p class="panel-eyebrow">Profile</p><h2 class="panel-title">Update your details</h2>@if(session('status')==='profile-updated')<div class="alert alert-success">Your profile has been updated.</div>@endif<form method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')<div><label class="form-label" for="account-name">Name</label><input class="form-control" id="account-name" name="name" value="{{ old('name',$user->name) }}" maxlength="255" required autocomplete="name">@error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div><div><label class="form-label" for="account-email">Email</label><input class="form-control" id="account-email" type="email" name="email" value="{{ old('email',$user->email) }}" maxlength="255" required autocomplete="email">@error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div><button class="btn btn-dark" type="submit">Save details</button></form></section>
    </div>
    <section class="customer-panel rewards-panel">
        <header><p class="panel-eyebrow">Rewards</p><h2 class="panel-title">Your points</h2></header>
        <div class="rewards-summary"><div><span>Current points</span><strong>{{ number_format($user->loyalty_points) }}</strong></div><div><span>Redeemable value</span><strong>{{ number_format($user->loyalty_points * .10, 2) }} EGP</strong></div></div>
        <div class="rewards-rules"><p>Earn 1 point for every 10 EGP spent.</p><p>Redeem 100 points for 10 EGP.</p></div>
        <div class="rewards-activity"><h3>Recent activity</h3>@forelse($loyaltyActivity as $entry)<div><span>{{ $entry->description }}</span><strong>{{ $entry->points_change > 0 ? '+' : '' }}{{ number_format($entry->points_change) }}</strong></div>@empty<p>No points activity yet.</p>@endforelse</div>
    </section>
</div></section>
@endsection
