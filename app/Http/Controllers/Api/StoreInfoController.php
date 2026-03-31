<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Settings\TenantSettings;
use Illuminate\Http\JsonResponse;

class StoreInfoController extends Controller
{
    public function __invoke(TenantSettings $settings): JsonResponse
    {
        return ApiResponse::success([
            'store_name' => $settings->storeName,
            'tagline' => $settings->storeTagline ?? '',
            'phone' => $settings->storePhone ?? '',
            'email' => $settings->storeEmail ?? '',
            'address' => $settings->storeAddress ?? '',
            'logo_url' => $settings->storeLogoUrl() ?? '',
            'colors' => [
                'primary' => $settings->brandColorPrimary,
            ],
            'hours' => $settings->operatingHours,
            'social_links' => $settings->socialMediaLinks,
        ], 'Store info retrieved successfully.');
    }
}
