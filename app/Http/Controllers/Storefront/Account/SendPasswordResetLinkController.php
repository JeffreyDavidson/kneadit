<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::broker('customers')->sendResetLink($request->only('email'));

        // Always report success to avoid leaking which emails have accounts.
        return back()->with('status', 'If that email matches an account, a reset link has been sent.');
    }
}
