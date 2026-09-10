<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\VerificationRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(VerificationRequirement $verification): View
    {
        return view('admin.settings.edit', ['verificationRequired' => $verification->required()]);
    }

    public function update(Request $request, VerificationRequirement $verification): RedirectResponse
    {
        $validated = $request->validate(['require_customer_email_verification' => ['required', 'boolean']]);
        $verification->update((bool) $validated['require_customer_email_verification']);

        return back()->with('success', 'Verification setting updated.');
    }
}
