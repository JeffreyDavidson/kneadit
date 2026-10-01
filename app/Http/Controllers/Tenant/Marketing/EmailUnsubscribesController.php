<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Marketing;

use App\Actions\Marketing\ResubscribeCustomerToMarketing;
use App\Actions\Marketing\UnsubscribeCustomerFromMarketing;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

/**
 * The customer-facing end of the unsubscribe link in marketing emails.
 *
 * All three routes sit behind the `signed:relative` middleware: the signature in
 * the emailed link is the only proof of who is asking, so there is no login.
 * `store` unsubscribes immediately (no confirmation) because mail providers
 * POST to the link for one-click unsubscribe (RFC 8058) without a CSRF token.
 */
class EmailUnsubscribesController extends Controller
{
    public function store(Customer $customer, UnsubscribeCustomerFromMarketing $unsubscribe, TenantSettings $settings): View
    {
        $unsubscribe($customer);

        return view('tenant.storefront.email-unsubscribe', [
            'settings' => $settings,
            'customer' => $customer,
            'resubscribed' => false,
        ]);
    }

    public function show(Customer $customer, TenantSettings $settings): View
    {
        return view('tenant.storefront.email-unsubscribe', [
            'settings' => $settings,
            'customer' => $customer,
            'resubscribed' => false,
        ]);
    }

    public function destroy(Customer $customer, ResubscribeCustomerToMarketing $resubscribe, TenantSettings $settings): View
    {
        $resubscribe($customer);

        return view('tenant.storefront.email-unsubscribe', [
            'settings' => $settings,
            'customer' => $customer,
            'resubscribed' => true,
        ]);
    }
}
