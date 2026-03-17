<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BillingPortalController extends Controller
{
    /**
     * Redirect to Stripe Customer Portal for managing subscription.
     */
    public function __invoke(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('filament.admin.pages.dashboard'));
    }
}
