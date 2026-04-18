<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Actions\Customers\RegisterCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\RegisterCustomerRequest;
use Illuminate\Http\RedirectResponse;

class RegisterCustomerController extends Controller
{
    public function __invoke(RegisterCustomerRequest $request, RegisterCustomer $register): RedirectResponse
    {
        $customer = $register($request->validated());

        auth('customer')->login($customer, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }
}
