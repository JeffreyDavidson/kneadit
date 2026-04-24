<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResendCustomerVerificationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        if ($customer->hasVerifiedEmail()) {
            return to_route('account.dashboard');
        }

        $customer->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been emailed to you.');
    }
}
