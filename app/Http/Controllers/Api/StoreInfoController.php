<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class StoreInfoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'store_name' => Setting::get('store_name', ''),
                'tagline' => Setting::get('tagline', ''),
                'phone' => Setting::get('phone', ''),
                'email' => Setting::get('email', ''),
                'address' => Setting::get('address', ''),
                'logo_url' => Setting::get('logo_url', ''),
                'colors' => [
                    'primary' => Setting::get('color_primary', '#e11d48'),
                    'secondary' => Setting::get('color_secondary', '#be123c'),
                    'accent' => Setting::get('color_accent', '#f43f5e'),
                    'light' => Setting::get('color_light', '#fff1f2'),
                    'border' => Setting::get('color_border', '#fecdd3'),
                    'muted' => Setting::get('color_muted', '#6b7280'),
                ],
                'hours' => Setting::get('hours', ''),
                'social_links' => [
                    'facebook' => Setting::get('social_facebook', ''),
                    'instagram' => Setting::get('social_instagram', ''),
                    'twitter' => Setting::get('social_twitter', ''),
                    'tiktok' => Setting::get('social_tiktok', ''),
                ],
            ],
            'message' => 'Store info retrieved successfully.',
        ]);
    }
}
