<?php

namespace App\Http\Controllers\Storefront\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowEmailVerifyNoticeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        /** @var MustVerifyEmail $customer */
        $customer = auth('customer')->user();

        if ($customer->hasVerifiedEmail()) {
            return to_route('account.dashboard');
        }

        return view('storefront.account.verify-notice');
    }
}
