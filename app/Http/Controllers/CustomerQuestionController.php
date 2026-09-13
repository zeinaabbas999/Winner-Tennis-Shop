<?php

namespace App\Http\Controllers;

use App\Models\CustomerQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerQuestionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email:rfc', 'max:255'], 'question' => ['required', 'string', 'max:2000']]);
        CustomerQuestion::create($data + ['user_id' => $request->user()?->id]);

        return back()->with('success', 'Your question has been sent to WINNER.');
    }
}
