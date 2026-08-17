<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Tenants\CompleteTenantOnboarding;
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
        CompleteTenantOnboarding $completeOnboarding,
    ): RedirectResponse {
        $adminUrl = $request->adminUrl();

        $completeOnboarding(
            user: $user,
            storeName: $request->string('store_name')->toString(),
            subdomain: $request->subdomain(),
            useKneadItStorefront: $request->usesKneadItStorefront(),
            externalWebsite: $request->filled('external_website') ? $request->string('external_website')->toString() : null,
            referralCode: $request->referralCode(),
            adminUrl: $adminUrl,
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($adminUrl);
    }
}
