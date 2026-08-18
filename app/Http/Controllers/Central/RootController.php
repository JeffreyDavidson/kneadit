<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\HomeController;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RootController extends Controller
{
    public function __invoke(HomeController $homeController): View|Response
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return view('platform.welcome');
        }

        $externalUrl = $tenant->external_website;

        if (! $tenant->storefront_enabled && is_string($externalUrl) && Str::isUrl($externalUrl, ['http', 'https'])) {
            return redirect()->away($externalUrl);
        }

        if (! $tenant->storefront_enabled) {
            $settings = resolve(TenantSettings::class);

            return response()->view('platform.storefront-disabled', [
                'storeName' => $settings->store->name,
                'tenant' => $tenant,
            ]);
        }

        return $homeController(resolve(TenantSettings::class));
    }
}
