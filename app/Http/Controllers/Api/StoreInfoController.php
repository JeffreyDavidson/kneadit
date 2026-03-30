<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class StoreInfoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'store_name' => settings('store_name', ''),
            'tagline' => settings('tagline', ''),
            'phone' => settings('phone', ''),
            'email' => settings('email', ''),
            'address' => settings('address', ''),
            'logo_url' => settings('logo_url', ''),
            'colors' => [
                'primary' => settings('color_primary', '#e11d48'),
                'secondary' => settings('color_secondary', '#be123c'),
                'accent' => settings('color_accent', '#f43f5e'),
                'light' => settings('color_light', '#fff1f2'),
                'border' => settings('color_border', '#fecdd3'),
                'muted' => settings('color_muted', '#6b7280'),
            ],
            'hours' => settings('hours', ''),
            'social_links' => [
                'facebook' => settings('social_facebook', ''),
                'instagram' => settings('social_instagram', ''),
                'twitter' => settings('social_twitter', ''),
                'tiktok' => settings('social_tiktok', ''),
            ],
        ], 'Store info retrieved successfully.');
    }
}
