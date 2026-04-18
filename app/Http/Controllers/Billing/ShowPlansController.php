<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowPlansController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('billing.plans', [
            'plans' => config('kneadit.plans'),
            'currentPlan' => $user instanceof User ? $user->current_plan?->value : null,
            'bakeryName' => session('bakery_name'),
        ]);
    }
}
