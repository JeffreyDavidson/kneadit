<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    protected array $routePageMap = [
        'storefront.menu' => 'menu',
        'storefront.home' => 'home',
        'storefront.about' => 'about',
        'storefront.reviews' => 'reviews',
        'order.create' => 'order',
        'order.confirmation' => 'order',
        'order.track' => 'track',
        'contact.show' => 'contact',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests that return HTML
        if (! $request->isMethod('GET') || $request->ajax()) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        $page = $this->routePageMap[$routeName] ?? null;

        // If no route name matched, try to detect from path
        if (! $page) {
            $path = trim($request->path(), '/');
            if ($path === '' || $path === '/') {
                $page = 'home';
            }
        }

        if (! $page) {
            return $response;
        }

        $sessionId = $request->session()->getId();

        // Throttle: one view per session per page per hour (using session to avoid cache tagging issues with Stancl)
        $throttleKey = "pv_tracked:{$page}";
        $trackedAt = $request->session()->get($throttleKey);

        if ($trackedAt && now()->diffInMinutes(\Carbon\Carbon::parse($trackedAt)) < 60) {
            return $response;
        }

        $request->session()->put($throttleKey, now()->toISOString());

        try {
            PageView::create([
                'page' => $page,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'created_at' => now(),
            ]);

            // Track product views on menu/home pages
            if (in_array($page, ['menu', 'home'])) {
                $productThrottleKey = "pv_products_tracked:{$page}";
                $productsTrackedAt = $request->session()->get($productThrottleKey);

                if (! $productsTrackedAt || now()->diffInMinutes(\Carbon\Carbon::parse($productsTrackedAt)) >= 60) {
                    $request->session()->put($productThrottleKey, now()->toISOString());
                    $products = Product::where('is_active', true)->pluck('id');
                    foreach ($products as $productId) {
                        PageView::create([
                            'page' => $page,
                            'product_id' => $productId,
                            'session_id' => $sessionId,
                            'ip_address' => $request->ip(),
                            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if page_views table doesn't exist yet
        }

        return $response;
    }
}
