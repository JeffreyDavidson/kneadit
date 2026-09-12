<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

class BillingPortalController extends Controller
{
    public function __invoke(#[CurrentUser] User $user): RedirectResponse
    {
        if (! $user->hasStripeId()) {
            return to_route('billing.plans')
                ->with('error', 'No billing account found. Choose a plan to start billing.');
        }

        return $user->redirectToBillingPortal(route('filament.admin.pages.dashboard'));
    }
}
