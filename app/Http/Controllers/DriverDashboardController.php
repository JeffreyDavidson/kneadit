<?php

namespace App\Http\Controllers;

use App\Queries\DriverDeliveryQuery;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class DriverDashboardController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        return view('driver', [
            'settings' => $settings,
            'orders' => DriverDeliveryQuery::forDate(today()),
        ]);
    }
}
