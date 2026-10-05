<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

class BillingPortalController extends Controller
{
    public function __invoke(#[CurrentUser] User $user, TenantUrlGenerator $tenantUrls): RedirectResponse
    {
        if (! $user->hasStripeId()) {
            return to_route('billing.plans')
                ->with('error', 'No billing account found. Choose a plan to start billing.');
        }

        // The owner works in their bakery admin, so that is where the Stripe portal returns to.
        $bakery = $user->tenants()->first();

        return $user->redirectToBillingPortal(
            $bakery instanceof Tenant ? $tenantUrls->admin($bakery) : route('billing.plans'),
        );
    }
}
