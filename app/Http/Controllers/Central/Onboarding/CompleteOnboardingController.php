<?php

namespace App\Http\Controllers\Central\Onboarding;

use App\Actions\Tenants\CompleteTenantOnboarding;
use App\Exceptions\Platform\UserAlreadyHasBakeryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreOnboardingRequest;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CompleteOnboardingController extends Controller
{
    public function __invoke(
        StoreOnboardingRequest $request,
        #[CurrentUser]
        User $user,
        CompleteTenantOnboarding $completeOnboarding,
        TenantUrlGenerator $tenantUrls,
    ): RedirectResponse {
        try {
            $tenant = $completeOnboarding(
                user: $user,
                storeName: $request->string('store_name')->toString(),
                subdomain: $request->subdomain(),
                useKneadItStorefront: $request->usesKneadItStorefront(),
                externalWebsite: $request->filled('external_website') ? $request->string('external_website')->toString() : null,
                referralCode: $request->referralCode(),
            );
        } catch (UserAlreadyHasBakeryException $exception) {
            return redirect()
                ->away($tenantUrls->admin($user->tenants()->firstOrFail()))
                ->with('error', $exception->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($tenantUrls->admin($tenant));
    }
}
