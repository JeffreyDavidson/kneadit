<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreInfoResource;
use App\Services\Settings\TenantSettings;

class StoreInfoController extends Controller
{
    public function __invoke(TenantSettings $settings): StoreInfoResource
    {
        return new StoreInfoResource($settings);
    }
}
