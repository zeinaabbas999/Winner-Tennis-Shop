<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolicyController extends Controller
{
    public function index(): View
    {
        return view('admin.policies.index', ['policies' => PolicyPage::orderBy('id')->get()]);
    }

    public function edit(PolicyPage $policyPage): View
    {
        return view('admin.policies.edit', compact('policyPage'));
    }

    public function update(Request $request, PolicyPage $policyPage): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:100'],
            'intro' => ['nullable', 'string', 'max:1000'],
            'effective_date' => ['nullable', 'date'],
            'is_visible' => ['required', 'boolean'],
            'sections' => ['required', 'array', 'min:1', 'max:30'],
            'sections.*.heading' => ['required', 'string', 'max:255'],
            'sections.*.content' => ['required', 'string', 'max:10000'],
        ]);
        $policyPage->update($data);

        return to_route('admin.policies.index')->with('success', 'Policy content updated.');
    }
}
