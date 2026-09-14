<x-guest-layout>
    <div class="auth-form-heading">
        <p class="eyebrow text-green">Account access</p>
        <h1>Forgot your password?</h1>
        <p>Enter your email and we will send you a secure password reset link.</p>
    </div>
    <x-auth-session-status class="alert alert-success" :status="session('status')" />
    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">@error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <button class="btn btn-dark" type="submit">Send reset link</button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Back to login</a></p>
</x-guest-layout>
