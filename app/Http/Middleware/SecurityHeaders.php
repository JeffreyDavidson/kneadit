<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security headers to every response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy in Report-Only mode. Permissive baseline that
        // mirrors current usage — once the violation log is quiet, tighten by
        // dropping 'unsafe-inline' (will require nonce'ing the ~34 inline
        // <script>/<style> blocks across blades) and switch to enforcement.
        $response->headers->set('Content-Security-Policy-Report-Only', $this->csp());

        return $response;
    }

    private function csp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.usefathom.com https://js.stripe.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self' https://cdn.usefathom.com https://api.stripe.com",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.stripe.com",
            "frame-ancestors 'self'",
            'report-uri ' . route('csp.report'),
        ]);
    }
}
