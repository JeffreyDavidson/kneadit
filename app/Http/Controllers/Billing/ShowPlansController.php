<?php

namespace App\Http\Controllers\Billing;

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;

class ShowPlansController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function __invoke(#[CurrentUser] ?User $user, TenantUrlGenerator $tenantUrls): View
    {
        $tenant = $user?->tenants()->first();
        $hasTenant = $tenant instanceof Tenant;

        return view('central.billing.plans', [
            'plans' => config('kneadit.plans'),
            'currentPlan' => $user instanceof User ? SubscriptionTier::resolve($user)?->value : null,
            'bakeryName' => $hasTenant ? ($tenant->store_name ?: $tenant->name) : session('bakery_name'),
            'bakeryAdminUrl' => $hasTenant ? $tenantUrls->admin($tenant) : null,
            'trialEndsAt' => $hasTenant && $tenant->trial_ends_at?->isFuture() ? $tenant->trial_ends_at : null,
            'hasBillingAccount' => $user instanceof User && $user->hasStripeId(),
            'isFreeForever' => $hasTenant && $tenant->free_forever,
        ]);
    }
}
