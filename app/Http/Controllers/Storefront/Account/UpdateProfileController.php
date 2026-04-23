<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\UpdateCustomerProfileRequest;
use App\Models\Customers\Customer;
use Illuminate\Http\RedirectResponse;

class UpdateProfileController extends Controller
{
    public function __invoke(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = $request->user('customer');

        $customer->forceFill($request->validated())->save();

        return to_route('account.profile.show')->with('status', 'Profile updated.');
    }
}
