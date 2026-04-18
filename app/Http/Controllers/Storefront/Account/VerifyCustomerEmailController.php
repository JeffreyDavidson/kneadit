<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\CustomerEmailVerificationRequest;
use App\Models\Customers\Customer;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyCustomerEmailController extends Controller
{
    public function __invoke(CustomerEmailVerificationRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if ($customer->hasVerifiedEmail()) {
            return redirect()->route('account.dashboard')->with('status', 'Your email is already verified.');
        }

        if ($customer->markEmailAsVerified()) {
            event(new Verified($customer));
        }

        return redirect()->route('account.dashboard')->with('status', 'Your email has been verified.');
    }
}
