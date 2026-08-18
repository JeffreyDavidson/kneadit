<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\ResetPasswordRequest;
use App\Models\Customers\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password): void {
                $customer->forceFill([
                    'password' => $password,
                ])->save();
            },
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages([
                'email' => trans(is_string($status) ? $status : 'passwords.user'),
            ]);
        }

        return to_route('account.login.show')
            ->with('status', 'Your password has been reset. You can sign in now.');
    }
}
