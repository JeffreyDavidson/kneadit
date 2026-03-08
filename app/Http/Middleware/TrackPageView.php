<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $cacheKey = "pv:{$sessionId}:{$page}";

        // Throttle: one view per session per page per hour
        if (Cache::has($cacheKey)) {
            return $response;
        }

        Cache::put($cacheKey, true, now()->addHour());

        PageView::create([
            'page' => $page,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'created_at' => now(),
        ]);

        // Track product views on menu/home pages
        if (in_array($page, ['menu', 'home'])) {
            $products = Product::where('is_active', true)->pluck('id');
            foreach ($products as $productId) {
                $productCacheKey = "pv:{$sessionId}:product:{$productId}";
                if (! Cache::has($productCacheKey)) {
                    Cache::put($productCacheKey, true, now()->addHour());
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

        return $response;
    }
}
