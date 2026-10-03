<?php

namespace App\Http\Controllers\Central\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowOnboardingController extends Controller
{
    public function __invoke(
        #[CurrentUser]
        User $user,
        TenantUrlGenerator $tenantUrls,
    ): View|RedirectResponse {
        $bakery = $user->tenants()->first();

        if ($bakery !== null) {
            return redirect()->away($tenantUrls->admin($bakery));
        }

        return view('central.platform.onboarding', [
            'bakeryName' => session('bakery_name', ''),
        ]);
    }
}
