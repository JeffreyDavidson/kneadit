<?php

use App\Services\Settings\PlatformSettingsManager;
use App\Services\Settings\SettingsManager;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

if (! function_exists('settings')) {
    /**
     * Get or set a tenant setting value.
     *
     * @param string|array<string, mixed> $key
     */
    function settings(string|array $key, mixed $default = null): mixed
    {
        $manager = resolve(SettingsManager::class);

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $manager->set($k, $v);
            }

            return null;
        }

        return $manager->get($key, $default);
    }
}

if (! function_exists('settingsPageContent')) {
    /**
     * Get all page content for a specific page.
     *
     * @return array<string, mixed>
     */
    function settingsPageContent(string $page): array
    {
        return resolve(SettingsManager::class)->pageContentAll($page);
    }
}

if (! function_exists('clean')) {
    /**
     * Sanitize HTML content, allowing safe tags for rich text display.
     */
    function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias();

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}

if (! function_exists('platformSettings')) {
    /**
     * Get or set a platform setting value.
     *
     * @param string|array<string, mixed> $key
     */
    function platformSettings(string|array $key, mixed $default = null): mixed
    {
        $manager = resolve(PlatformSettingsManager::class);

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $manager->set($k, $v);
            }

            return null;
        }

        return $manager->get($key, $default);
    }
}
