<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerQuestion;
use App\Models\Order;
use App\Models\ReturnExchangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequestController extends Controller
{
    public function index(): View
    {
        return view('admin.requests.index', [
            'cancellations' => Order::with('user')->whereNotNull('cancellation_status')->latest('cancellation_requested_at')->paginate(15, ['*'], 'cancellations'),
            'returns' => ReturnExchangeRequest::with(['order', 'orderItem', 'user'])->latest()->paginate(15, ['*'], 'returns'),
            'questions' => CustomerQuestion::latest()->paginate(15, ['*'], 'questions'),
        ]);
    }

    public function reviewReturn(Request $request, ReturnExchangeRequest $returnExchangeRequest): RedirectResponse
    {
        abort_unless($returnExchangeRequest->status === 'pending', 422);
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])], 'admin_note' => ['nullable', 'string', 'max:1500']]);
        $returnExchangeRequest->update(['status' => $data['decision'], 'admin_note' => $data['admin_note'] ?? null, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        return back()->with('success', 'Return or exchange request updated.');
    }

    public function updateQuestion(Request $request, CustomerQuestion $customerQuestion): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'resolved'])], 'admin_response' => ['nullable', 'string', 'max:2000']]);
        $customerQuestion->update($data + ['responded_by' => $request->user()->id, 'responded_at' => $data['status'] === 'resolved' ? now() : null]);

        return back()->with('success', 'Customer question updated.');
    }
}
