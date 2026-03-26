<?php

namespace App\Services;

use App\Models\PageView;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class PageViewTracker
{
    /** @var array<string, string> */
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

    public function detectPage(?string $routeName, string $path): ?string
    {
        if ($routeName && isset($this->routePageMap[$routeName])) {
            return $this->routePageMap[$routeName];
        }

        $trimmedPath = trim($path, '/');
        if ($trimmedPath === '' || $trimmedPath === '/') {
            return 'home';
        }

        return null;
    }

    public function track(Request $request): void
    {
        $routeName = $request->route()?->getName();
        $page = $this->detectPage($routeName, $request->path());

        if (! $page) {
            return;
        }

        $sessionId = $request->session()->getId();

        if ($this->isThrottled($request, "pv_tracked:{$page}")) {
            return;
        }

        $request->session()->put("pv_tracked:{$page}", now()->toISOString());

        try {
            PageView::query()->create([
                'page' => $page,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'created_at' => now(),
            ]);

            $this->trackProductViews($request, $page, $sessionId);
        } catch (\Exception) {
            // Silently fail if page_views table doesn't exist yet
        }
    }

    protected function isThrottled(Request $request, string $key): bool
    {
        $trackedAt = $request->session()->get($key);

        return $trackedAt && now()->diffInMinutes(Date::parse($trackedAt)) < 60;
    }

    protected function trackProductViews(Request $request, string $page, string $sessionId): void
    {
        if (! in_array($page, ['menu', 'home'])) {
            return;
        }

        $throttleKey = "pv_products_tracked:{$page}";
        if ($this->isThrottled($request, $throttleKey)) {
            return;
        }

        $request->session()->put($throttleKey, now()->toISOString());

        $products = Product::query()->active()->pluck('id');
        $ipAddress = $request->ip();
        $userAgent = substr($request->userAgent() ?? '', 0, 255);
        $timestamp = now();

        $records = $products->map(fn (int $productId) => [
            'page' => $page,
            'product_id' => $productId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => $timestamp,
        ])->all();

        PageView::query()->insert($records);
    }
}
