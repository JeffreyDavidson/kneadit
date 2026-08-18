<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Orders\OrderModificationGuard;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowOrderConfirmationController extends Controller
{
    public function __invoke(Order $order, TenantSettings $settings, SettingsManager $manager, OrderModificationGuard $guard): View
    {
        $order->load('orderItems.product');

        $content = settingsPageContent('order_confirmation');

        $storedSteps = $manager->get('order_journey_steps');
        $journeySteps = $storedSteps
            ? (json_decode($storedSteps, true) ?: config('kneadit.default_journey_steps'))
            : config('kneadit.default_journey_steps');

        $referralCode = $settings->engagement->customerReferralProgramEnabled
            ? $order->customer?->referral_code
            : null;

        return view('storefront.order-confirmation', [
            'settings' => $settings,
            'storefrontTheme' => (string) settings('storefront_theme', 'classic'),
            'order' => $order,
            'content' => $content,
            'journeySteps' => $journeySteps,
            'canModify' => $guard->canModify($order),
            'modifyMinutesRemaining' => $guard->minutesRemaining($order),
            'referralCode' => $referralCode,
            'referralShareUrl' => $referralCode ? route('customer.referral', [
                'code' => $referralCode,
            ]) : null,
        ]);
    }
}
