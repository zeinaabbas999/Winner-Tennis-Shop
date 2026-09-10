<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\VerificationRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request, VerificationRequirement $verification): RedirectResponse|View
    {
        return ! $verification->required() || $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('orders.index', absolute: false))
                    : view('auth.verify-email');
    }
}
