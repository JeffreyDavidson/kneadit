<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    /**
     * Generate the PWA manifest.json for the storefront.
     */
    public function __invoke(): JsonResponse
    {
        $storeName = settings('store_name', 'Our Bakery');
        $primaryColor = tenant()->brand_color_primary ?? '#d4920c';

        return response()->json([
            'name' => $storeName,
            'short_name' => $storeName,
            'description' => settings('business_tagline', 'Fresh baked goods made with love'),
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#fef9ef',
            'theme_color' => $primaryColor,
            'icons' => [
                [
                    'src' => '/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}
