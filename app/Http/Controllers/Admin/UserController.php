<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Loyalty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'verification' => ['nullable', Rule::in(['verified', 'unverified'])],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
        ]);

        $users = User::withTrashed()
            ->where('role', 'user')
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($filters['search'] ?? null, function ($query, $term) {
                $query->where(fn ($nested) => $nested
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when(($filters['verification'] ?? null) === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when(($filters['verification'] ?? null) === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->whereNull('deleted_at'))
            ->when(($filters['status'] ?? null) === 'archived', fn ($query) => $query->whereNotNull('deleted_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(int $user): View
    {
        $customer = $this->customer($user);
        $orders = $customer->orders()->latest()->paginate(15);

        return view('admin.users.show', [
            'customer' => $customer,
            'orders' => $orders,
            'totalSpent' => $customer->orders()->sum('total'),
            'loyaltyActivity' => $customer->loyaltyTransactions()->with('order', 'admin')->latest()->limit(20)->get(),
            'lifetimeEarned' => $customer->loyaltyTransactions()->where('points_change', '>', 0)->sum('points_change'),
            'lifetimeUsed' => abs($customer->loyaltyTransactions()->where('points_change', '<', 0)->sum('points_change')),
        ]);
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        $customer = $this->customer($user);
        abort_if($customer->is($request->user()) || $customer->isAdmin(), 403);
        $customer->delete();

        return to_route('admin.users.index')->with('success', 'Customer account archived. Order history was preserved.');
    }

    public function restore(int $user): RedirectResponse
    {
        $customer = $this->customer($user);
        $customer->restore();

        return back()->with('success', 'Customer account restored.');
    }

    public function adjustPoints(Request $request, int $user, Loyalty $loyalty): RedirectResponse
    {
        $customer = $this->customer($user);
        $data = $request->validate([
            'points_change' => ['required', 'integer', 'not_in:0', 'between:-1000000,1000000'],
            'reason' => ['required', 'string', 'max:500'],
        ]);
        DB::transaction(fn () => $loyalty->adjust($customer, $request->user(), $data['points_change'], $data['reason']));

        return back()->with('success', 'Points balance adjusted and recorded.');
    }

    private function customer(int $id): User
    {
        return User::withTrashed()->where('role', 'user')->findOrFail($id);
    }
}
