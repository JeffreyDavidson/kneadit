<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\UpdateCustomerProfileRequest;
use App\Models\Customers\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        return view('storefront.account.profile', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $customer->forceFill($request->validated())->save();

        return to_route('account.profile.show')->with('status', 'Profile updated.');
    }
}
