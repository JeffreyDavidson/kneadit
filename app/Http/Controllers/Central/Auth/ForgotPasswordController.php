<?php

namespace App\Http\Controllers\Central\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->validated());

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => __($status),
            ]);
        }

        // Same answer whether or not an account has this email, so the form can't be used to find accounts.
        return back()->with('status', "If an account exists for that email, we've sent a reset link.");
    }

    public function show(): View
    {
        return view('central.auth.forgot-password');
    }
}
