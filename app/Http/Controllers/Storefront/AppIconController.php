<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Settings\TenantSettings;
use App\Services\Support\AppIconGeneratorService;
use Illuminate\Http\Response;

class AppIconController extends Controller
{
    public function __invoke(string $size, TenantSettings $settings, AppIconGeneratorService $generator): Response
    {
        $size = in_array($size, ['192', '512']) ? (int) $size : 192;

        $data = $generator->generate($size, $settings->branding->brandColorPrimary, $settings->storeName);

        return response($data)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
