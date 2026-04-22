<?php

namespace App\Http\Controllers\Billing;

use App\Enums\Platform\SubscriptionTier;
use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;

class ShowPlansController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function __invoke(#[CurrentUser] ?User $user): View
    {
        return view('billing.plans', [
            'plans' => config('kneadit.plans'),
            'currentPlan' => $user ? SubscriptionTier::resolve($user)?->value : null,
            'bakeryName' => session('bakery_name'),
        ]);
    }
}
