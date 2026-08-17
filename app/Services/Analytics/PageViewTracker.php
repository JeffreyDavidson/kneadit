<?php

namespace App\Services\Analytics;

use App\Actions\Analytics\RecordPageView;
use App\Actions\Analytics\RecordProductImpressions;
use App\Enums\Content\PageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Exceptions;

class PageViewTracker
{
    /** @var array<string, PageType> */
    protected array $routePageMap = [
        'storefront.menu' => PageType::Menu,
        'storefront.home' => PageType::Home,
        'storefront.about' => PageType::About,
        'storefront.reviews' => PageType::Reviews,
        'order.create' => PageType::Order,
        'order.confirmation' => PageType::OrderConfirmation,
        'order.track' => PageType::Track,
        'contact.show' => PageType::Contact,
    ];

    public function __construct(
        protected RecordPageView $recordPageView,
        protected RecordProductImpressions $recordProductImpressions,
        protected VisitorIdentifier $visitorIdentifier,
    ) {}

    public function detectPage(?string $routeName, string $path): ?string
    {
        if ($routeName && isset($this->routePageMap[$routeName])) {
            return $this->routePageMap[$routeName]->value;
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

        if ($this->isThrottled($request, "pv_tracked:{$page}")) {
            return;
        }

        $request->session()->put("pv_tracked:{$page}", now()->toISOString());

        $data = [
            'page' => $page,
            'session_id' => $this->visitorIdentifier->fromSessionId($request->session()->getId()),
        ];

        try {
            ($this->recordPageView)($data);

            if ($this->shouldTrackProductImpressions($request, $page)) {
                ($this->recordProductImpressions)($data);
            }
        } catch (\Throwable $exception) {
            Exceptions::report($exception);
        }
    }

    protected function isThrottled(Request $request, string $key): bool
    {
        $trackedAt = $request->session()->get($key);

        return $trackedAt && now()->diffInMinutes(Date::parse($trackedAt)) < 60;
    }

    protected function shouldTrackProductImpressions(Request $request, string $page): bool
    {
        if (! in_array($page, ['menu', 'home'])) {
            return false;
        }

        $throttleKey = "pv_products_tracked:{$page}";
        if ($this->isThrottled($request, $throttleKey)) {
            return false;
        }

        $request->session()->put($throttleKey, now()->toISOString());

        return true;
    }
}
