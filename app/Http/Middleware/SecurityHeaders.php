<?php

namespace App\Http\Middleware;

use App\Support\Csp\CspNonce;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function __construct(private readonly CspNonce $nonce) {}

    /**
     * Add security headers to every response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set($this->cspHeader(), $this->csp());

        return $response;
    }

    private function cspHeader(): string
    {
        return config('csp.mode') === 'enforce'
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';
    }

    private function csp(): string
    {
        $nonce = $this->nonce->sourceList();

        // CSP3 split style-src/script-src into -elem (tags) and -attr
        // (inline attributes like style="..." / onclick="..."). Browsers
        // were supposed to fall back to the umbrella directive, but in
        // practice (Chromium, Firefox) the absence of explicit -attr
        // directives produces a torrent of "violation" reports against
        // the umbrella rule even when the umbrella rule allows the
        // source. Spelling out -elem / -attr explicitly silences that
        // noise so real violations are visible in the report log.
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' {$nonce} 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.usefathom.com https://js.stripe.com",
            "script-src-elem 'self' {$nonce} 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.usefathom.com https://js.stripe.com",
            "script-src-attr 'unsafe-inline'",
            "style-src 'self' {$nonce} 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "style-src-elem 'self' {$nonce} 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "style-src-attr 'unsafe-inline'",
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
