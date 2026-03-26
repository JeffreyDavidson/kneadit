<?php

namespace App\Services\Settings;

use App\Models\Setting;

class SettingsManager
{
    /** @var array<string, array<string, mixed>> */
    protected array $cache = [];

    protected function tenantCacheKey(): string
    {
        return tenant() ? tenant()->getTenantKey() : 'central';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadAll();

        $tenantKey = $this->tenantCacheKey();

        return $this->cache[$tenantKey][$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        $tenantKey = $this->tenantCacheKey();

        if (isset($this->cache[$tenantKey])) {
            $this->cache[$tenantKey][$key] = $value;
        }
    }

    public function loadAll(): void
    {
        $tenantKey = $this->tenantCacheKey();

        if (isset($this->cache[$tenantKey])) {
            return;
        }

        $this->cache[$tenantKey] = Setting::query()->pluck('value', 'key')->all();
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

    public function flushCache(): void
    {
        $this->cache = [];
    }
}
