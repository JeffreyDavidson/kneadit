<?php

namespace App\Services;

use App\Models\PlatformSetting;

class PlatformSettingsManager
{
    /** @var array<string, mixed>|null */
    protected ?array $cache = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadAll();

        return $this->cache[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        PlatformSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        if ($this->cache !== null) {
            $this->cache[$key] = $value;
        }
    }

    public function loadAll(): void
    {
        if ($this->cache !== null) {
            return;
        }

        $this->cache = PlatformSetting::query()->pluck('value', 'key')->all();
    }

    public function flushCache(): void
    {
        $this->cache = null;
    }
}
