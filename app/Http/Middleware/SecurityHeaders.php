<?php

namespace App\Http\Middleware;

use App\Support\Csp\CspNonce;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /** Middleware parameter that leaves out the CSP, for the Filament panels. */
    public const string WITHOUT_CSP = 'without-csp';

    public function __construct(private readonly CspNonce $nonce) {}

    /**
     * The middleware string for the Filament panels. They get the frame, content-type,
     * referrer and permissions headers but not the CSP, because Filament and Livewire
     * print inline scripts that carry no nonce, which an enforced CSP would block.
     */
    public static function withoutCsp(): string
    {
        return sprintf('%s:%s', self::class, self::WITHOUT_CSP);
    }

    /**
     * Add security headers to every response.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$options): Response
    {
        if (! in_array(self::WITHOUT_CSP, $options, true)) {
            // Livewire prints its own <style> and <script> tags when it injects its assets, and
            // reads the nonce from Vite. Set it before the response is built so those carry it.
            Vite::useCspNonce($this->nonce->value());
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');

        if (in_array(self::WITHOUT_CSP, $options, true)) {
            return $response;
        }

        $response->headers->set($this->cspHeader(), $this->csp());

        return $response;
    }

    private function cspHeader(): string
    {
        return config('csp.mode') === 'report-only'
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';
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
        //
        // maps.googleapis.com / maps.gstatic.com (script) and maps.googleapis.com /
        // places.googleapis.com (connect) are for the Google Places address
        // suggestions (resources/js/address-input.js); img-src already allows https:.
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' {$nonce} 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.usefathom.com https://js.stripe.com https://maps.googleapis.com https://maps.gstatic.com",
            "script-src-elem 'self' {$nonce} https://cdn.jsdelivr.net https://cdn.usefathom.com https://js.stripe.com https://maps.googleapis.com https://maps.gstatic.com",
            "script-src-attr 'unsafe-inline'",
            "style-src 'self' {$nonce} 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "style-src-elem 'self' {$nonce} 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "style-src-attr 'unsafe-inline'",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self' https://cdn.usefathom.com https://api.stripe.com https://maps.googleapis.com https://places.googleapis.com",
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://checkout.stripe.com",
            "frame-ancestors 'self'",
            'report-uri '.route('csp.report'),
        ]);
    }
}
