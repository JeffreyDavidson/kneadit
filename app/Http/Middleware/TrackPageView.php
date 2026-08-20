<?php

namespace App\Http\Middleware;

use App\Services\Analytics\PageViewTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function __construct(
        protected PageViewTracker $tracker,
    ) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->ajax()) {
            return $response;
        }

        $this->tracker->track($request);

        return $response;
    }
}
