<x-guest-layout>
    <header class="auth-intro">
        <p class="eyebrow text-green">Secure your account</p>
        <h1>Verify your email</h1>
        <p>We sent a signed verification link to your email address. Open it to continue to checkout and your WINNER orders.</p>
    </header>

    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success" role="status">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="verification-note">
        <strong>Did not receive the message?</strong>
        <p>Check your spam folder, or request another secure link below.</p>
    </div>

    <div class="verification-actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn btn-dark" type="submit">Resend verification email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-dark" type="submit">Log out</button>
        </form>
    </div>
</x-guest-layout>
