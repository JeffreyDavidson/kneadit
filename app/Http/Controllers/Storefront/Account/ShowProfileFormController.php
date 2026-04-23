<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use Illuminate\Contracts\View\View;

class ShowProfileFormController extends Controller
{
    public function __invoke(): View
    {
        /** @var Customer $customer */
        $customer = auth('customer')->user();

        return view('storefront.account.profile', [
            'customer' => $customer,
        ]);
    }
}
