<?php

namespace App\Http\Middleware;

use App\Support\VerificationRequirement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCustomerEmailVerification
{
    public function __construct(private VerificationRequirement $verification) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->verification->required() && ! $request->user()?->hasVerifiedEmail()) {
            return redirect()->guest(route('verification.notice'));
        }

        return $next($request);
    }
}
