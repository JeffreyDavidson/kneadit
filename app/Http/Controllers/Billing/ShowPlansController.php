<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowPlansController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function __invoke(Request $request): View
    {
        return view('billing.plans', [
            'plans' => config('kneadit.plans'),
            'currentPlan' => $request->user()?->currentPlan()?->value,
            'bakeryName' => session('bakery_name'),
        ]);
    }
}
