<?php

namespace App\Services\Settings;

use App\Models\Platform\Setting;

class SettingsManager extends AbstractSettingsManager
{
    protected function cacheKey(): string
    {
        return tenant() ? tenant()->getTenantKey() : 'central';
    }

    protected function modelClass(): string
    {
        return Setting::class;
    }

    public function pageContent(string $page, string $key, mixed $default = ''): mixed
    {
        $content = json_decode($this->get('page_content', '{}'), true);

        return $content[$page][$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function pageContentAll(string $page): array
    {
        $content = json_decode($this->get('page_content', '{}'), true);

        return $content[$page] ?? [];
    }
}
