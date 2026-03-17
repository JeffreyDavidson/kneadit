<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowPlansController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function __invoke(Request $request)
    {
        return view('billing.plans', [
            'plans' => config('saas.plans'),
            'currentPlan' => $request->user()?->currentPlan(),
            'bakeryName' => session('bakery_name'),
        ]);
    }
}
