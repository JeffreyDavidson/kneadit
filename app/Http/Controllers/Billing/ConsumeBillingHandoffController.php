<?php

namespace App\Http\Controllers\Billing;

use App\Actions\Platform\ConsumeBillingHandoffToken;
use App\Exceptions\Platform\BillingHandoffRefusedException;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ConsumeBillingHandoffController extends Controller
{
    public function __invoke(
        string $token,
        Request $request,
        ConsumeBillingHandoffToken $consumeToken,
        TenantUrlGenerator $tenantUrls,
    ): RedirectResponse|Response {
        try {
            $user = $consumeToken($token, $request->ip());
        } catch (BillingHandoffRefusedException $exception) {
            return response()->view('central.billing.handoff-refused', [
                'adminUrl' => $exception->tenant instanceof Tenant ? $tenantUrls->admin($exception->tenant) : null,
            ], 403);
        }

        // Drop any earlier session, such as another account that was signed in on
        // this browser, so the owner starts from a clean one.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($user);

        return to_route('billing.plans');
    }
}
