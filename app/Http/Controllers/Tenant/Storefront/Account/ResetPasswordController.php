<?php

namespace App\Http\Controllers\Tenant\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\ResetPasswordRequest;
use App\Models\Customers\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): RedirectResponse
    {
        // Besides changing the password, a reset rotates the remember token (ending
        // remembered logins); AuthenticateCustomerSession ends the sessions that
        // were signed in with the old password.
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password): void {
                $customer->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $customer->save();

                event(new PasswordReset($customer));
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
