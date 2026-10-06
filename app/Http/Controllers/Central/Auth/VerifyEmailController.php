<?php

namespace App\Http\Controllers\Central\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * A verified owner continues to bakery setup, or to their bakery admin when
     * they already have a bakery.
     */
    public function __invoke(
        EmailVerificationRequest $request,
        #[CurrentUser]
        User $user,
        TenantUrlGenerator $tenantUrls,
    ): RedirectResponse {
        $request->fulfill();

        $bakery = $user->tenants()->first();

        if ($bakery === null) {
            return to_route('onboarding.show')->with('verified', true);
        }

        return redirect()->away($tenantUrls->admin($bakery))->with('verified', true);
    }
}
