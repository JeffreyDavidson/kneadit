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
        return $user->redirectToBillingPortal(route('filament.admin.pages.dashboard'));
    }
}
