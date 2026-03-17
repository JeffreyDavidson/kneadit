<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SwapPlanController extends Controller
{
    /**
     * Swap to a different plan.
     */
    public function __invoke(Request $request, string $plan)
    {
        $priceId = config("saas.stripe_prices.{$plan}");

        abort_unless($priceId, 404, 'Plan not found.');

        try {
            $request->user()->subscription('default')->swap($priceId);
        } catch (Exception $e) {
            Log::error('Plan swap failed', ['error' => $e->getMessage()]);

            return to_route('billing.plans')
                ->with('error', 'Unable to update your plan. Please try again or contact support.');
        }

        return to_route('billing.plans')
            ->with('success', 'Your plan has been updated!');
    }
}
