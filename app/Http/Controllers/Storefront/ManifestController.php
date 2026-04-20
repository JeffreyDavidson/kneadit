<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Settings\TenantSettings;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    /**
     * Generate the PWA manifest.json for the storefront.
     */
    public function __invoke(TenantSettings $settings): JsonResponse
    {
        $branding = $settings->branding;

        return response()->json([
            'name' => $settings->store->name,
            'short_name' => $settings->store->name,
            'description' => $branding->businessTagline ?? 'Fresh baked goods made with love',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#fef9ef',
            'theme_color' => $branding->brandColorPrimary,
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
