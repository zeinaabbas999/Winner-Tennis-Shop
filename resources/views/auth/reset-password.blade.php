<x-guest-layout>
    <div class="auth-form-heading">
        <p class="eyebrow text-green">Account access</p>
        <h1>Reset your password</h1>
        <p>Choose a strong new password for your WINNER account.</p>
    </div>
    <form class="auth-form" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">@error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div><label class="form-label" for="password">New password</label><input class="form-control" id="password" type="password" name="password" required autocomplete="new-password">@error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
        <div><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
        <button class="btn btn-dark" type="submit">Reset password</button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Back to login</a></p>
</x-guest-layout>
