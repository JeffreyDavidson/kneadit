<?php

namespace App\Http\Controllers\Billing;

use App\Actions\Platform\ResumeTenant;
use App\Actions\Stripe\ReauthenticateFromCheckoutSession;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutSuccessController extends Controller
{
    public function __invoke(
        Request $request,
        ReauthenticateFromCheckoutSession $reauthenticate,
        ResumeTenant $resumeTenant,
    ): RedirectResponse {
        if (! $request->user() && $request->has('session_id')) {
            $reauthenticate($request->string('session_id')->toString());
        }

        // The webhook normally resumes the bakery; this covers an owner who lands here
        // once their subscription is already recorded.
        $user = $request->user();

        if (! $user instanceof User || ! $user->subscribed('default')) {
            return to_route('onboarding.show');
        }

        $tenant = $user->tenants()->first();

        if ($tenant instanceof Tenant) {
            $resumeTenant($tenant);
        }

        return to_route('onboarding.show');
    }
}
