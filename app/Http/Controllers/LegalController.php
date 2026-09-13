<?php

namespace App\Http\Controllers;

use App\Models\PolicyPage;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        return $this->document('privacy');
    }

    public function terms(): View
    {
        return $this->document('terms');
    }

    public function cookies(): View
    {
        return $this->document('cookies');
    }

    public function policies(): View
    {
        $policies = PolicyPage::whereIn('key', ['shipping', 'returns', 'exchanges'])
            ->where('is_visible', true)->get()->keyBy('key');

        abort_if($policies->isEmpty(), 404);

        return view('pages.policies', compact('policies'));
    }

    private function document(string $key): View
    {
        $policy = PolicyPage::where('key', $key)->where('is_visible', true)->firstOrFail();

        return view('pages.legal', compact('policy'));
    }
}
