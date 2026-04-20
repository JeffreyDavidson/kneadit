<?php

namespace App\Mail\Concerns;

use App\Enums\Marketing\EmailTemplateType;
use App\Services\Email\EmailTemplateRenderer;

/**
 * Memoizes a single EmailTemplateRenderer::resolve() call so the
 * mailable's envelope() and content() methods don't re-resolve the
 * same template (and re-run all placeholder substitutions) twice
 * per dispatch.
 */
trait ResolvesTemplate
{
    /** @var array{subject: string, body: string|null}|null */
    private ?array $resolvedTemplateCache = null;

    private bool $resolvedTemplateLoaded = false;

    /**
     * @param array<string, string> $placeholders
     * @return array{subject: string, body: string|null}|null
     */
    protected function resolveTemplate(?EmailTemplateType $type, array $placeholders): ?array
    {
        if ($this->resolvedTemplateLoaded) {
            return $this->resolvedTemplateCache;
        }

        $this->resolvedTemplateLoaded = true;

        if ($type === null) {
            return $this->resolvedTemplateCache = null;
        }

        return $this->resolvedTemplateCache = app(EmailTemplateRenderer::class)->resolve($type, $placeholders);
    }
}
