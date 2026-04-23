<?php

namespace App\Support\Csp;

/**
 * Per-request Content-Security-Policy nonce. Resolved as a scoped singleton
 * (container scoped to the request lifecycle) so the same value flows from
 * the SecurityHeaders middleware (which writes it into the CSP header) into
 * every Blade @cspnonce directive call (which writes it into <script>/<style>
 * tags).
 */
final class CspNonce
{
    private string $value;

    public function __construct()
    {
        // 24 random bytes → ~32 base64 chars; CSP spec requires ≥128 bits of
        // entropy, this gives 192 bits with no padding.
        $this->value = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Build a CSP source-list token for this nonce. CSP requires the
     * literal string `'nonce-...'` (single-quoted) in the directive.
     */
    public function sourceList(): string
    {
        return "'nonce-{$this->value}'";
    }
}
