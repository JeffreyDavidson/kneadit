<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant && !$tenant->storefront_enabled) {
            // Redirect to external website if set
            if ($tenant->external_website) {
                return redirect()->away($tenant->external_website);
            }

            // Otherwise show disabled page
            return response()->view('storefront-disabled', [
                'storeName' => \App\Models\Setting::get('store_name', $tenant->store_name ?? 'Our Bakery'),
                'tenant' => $tenant,
            ]);
        }

        return $next($request);
    }
}
