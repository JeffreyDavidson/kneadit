<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Queries\Orders\DriverDeliveryQuery;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class DriverDashboardController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        return view('storefront.driver', [
            'settings' => $settings,
            'orders' => DriverDeliveryQuery::forDate(today()),
        ]);
    }
}
