<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Staff\User;
use Exception;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SwapPlanController extends Controller
{
    public function __invoke(#[CurrentUser] User $user, string $plan): RedirectResponse
    {
        $priceId = Config::get("kneadit.stripe_prices.{$plan}");
        abort_unless(is_string($priceId) && $priceId !== '', 404, 'Plan not found.');

        try {
            $user->subscription('default')?->swap($priceId);
        } catch (Exception $e) {
            Log::error('Plan swap failed', [
                'error' => $e->getMessage(),
            ]);

            return to_route('billing.plans')
                ->with('error', 'Unable to update your plan. Please try again or contact support.');
        }

        return to_route('billing.plans')
            ->with('success', 'Your plan has been updated!');
    }
}
