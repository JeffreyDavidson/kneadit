<?php

namespace App\Services\Email;

use App\Enums\Marketing\EmailTemplateType;
use App\Models\Marketing\EmailTemplate;

class EmailTemplateRenderer
{
    /**
     * Resolve a custom template for the given email type.
     *
     * Returns null if no custom template exists, allowing the Mailable
     * to fall back to its default Blade view.
     *
     * @param array<string, string> $placeholders
     * @return array{subject: string, body: string|null}|null
     */
    public function resolve(EmailTemplateType $type, array $placeholders = []): ?array
    {
        $template = EmailTemplate::query()
            ->where('email_type', $type)
            ->first();

        if (! $template) {
            return null;
        }

        return [
            'subject' => $this->replacePlaceholders($template->subject, $placeholders),
            'body' => $template->body
                ? $this->replacePlaceholders($template->body, $placeholders)
                : null,
        ];
    }

    /**
     * Replace {placeholder} tokens with actual values.
     *
     * @param array<string, string> $placeholders
     */
    public function replacePlaceholders(string $content, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        return $content;
    }

    /**
     * Render a default subject with placeholder replacement.
     *
     * Used by Mailables when no custom template exists but they
     * still want placeholder-based subject generation.
     *
     * @param array<string, string> $placeholders
     */
    public function renderDefaultSubject(EmailTemplateType $type, array $placeholders = []): string
    {
        return $this->replacePlaceholders($type->defaultSubject(), $placeholders);
    }
}
