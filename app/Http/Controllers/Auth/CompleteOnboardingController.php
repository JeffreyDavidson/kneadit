<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Tenants\CompleteReferral;
use App\Actions\Tenants\CreateTenant;
use App\Events\Platform\TenantOnboarded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOnboardingRequest;
use App\Models\Staff\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CompleteOnboardingController extends Controller
{
    public function __invoke(
        StoreOnboardingRequest $request,
        #[CurrentUser]
        User $user,
        CreateTenant $createTenant,
        CompleteReferral $completeReferral,
    ): RedirectResponse {
        $tenant = $createTenant(
            user: $user,
            storeName: $request->validated('store_name'),
            subdomain: $request->subdomain(),
            useKneadItStorefront: $request->usesKneadItStorefront(),
            externalWebsite: $request->validated('external_website'),
        );

        $completeReferral(
            referralCode: $request->referralCode(),
            tenantId: (string) $tenant->id,
            email: $user->email,
        );

        event(new TenantOnboarded($user, $tenant, $request->adminUrl()));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($request->adminUrl());
    }
}
