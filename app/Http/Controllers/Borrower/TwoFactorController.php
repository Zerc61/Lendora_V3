<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function edit(Request $request): View
    {
        return view('borrower.two-factor.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->user()->update([
            'email_otp_enabled' => $request->boolean('email_otp_enabled'),
        ]);

        return back()->with('status', __('messages.two_factor.updated'));
    }
}