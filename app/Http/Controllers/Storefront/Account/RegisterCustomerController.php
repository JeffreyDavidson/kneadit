<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Actions\Customers\RegisterCustomer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Account\RegisterCustomerRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;

class RegisterCustomerController extends Controller
{
    public function __invoke(RegisterCustomerRequest $request, RegisterCustomer $register): RedirectResponse
    {
        $customer = $register($request->validated());

        event(new Registered($customer));

        auth('customer')->login($customer, remember: true);

        $request->session()->regenerate();

        return to_route('account.email.verify.notice');
    }
}
