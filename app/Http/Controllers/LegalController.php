<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
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

        return view('pages.policies', ['policies' => $policies, 'eligibleItems' => $this->eligibleItems()]);
    }

    private function document(string $key): View
    {
        $policy = PolicyPage::where('key', $key)->where('is_visible', true)->firstOrFail();

        return view('pages.legal', ['policy' => $policy, 'eligibleItems' => $this->eligibleItems($key)]);
    }

    private function eligibleItems(?string $policy = null): array
    {
        if (! auth()->check()) {
            return [];
        }
        $types = $policy === 'returns' ? ['return'] : ($policy === 'exchanges' ? ['exchange'] : ['return', 'exchange']);

        return collect($types)->mapWithKeys(function ($type) {
            $items = OrderItem::query()->with('order')->whereHas('order', fn ($query) => $query->where('user_id', auth()->id())->whereIn('status', ['delivered', 'completed']))
                ->whereDoesntHave('returnExchangeRequests', fn ($query) => $query->where('type', $type))->latest()->get();

            return [$type => $items];
        })->all();
    }
}
