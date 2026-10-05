<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Http\Controllers\Controller;
use App\Queries\Orders\DriverDeliveryQuery;
use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class DriverDashboardController extends Controller
{
    public function __invoke(TenantSettings $settings, BakeryClock $clock): View
    {
        $today = $clock->today();

        return view('tenant.storefront.driver', [
            'settings' => $settings,
            'today' => $today,
            'orders' => DriverDeliveryQuery::forDate($today),
        ]);
    }
}
