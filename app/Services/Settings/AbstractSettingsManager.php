<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\DB;

abstract class AbstractSettingsManager
{
    /** @var array<string, array<string, mixed>> */
    protected array $cache = [];

    abstract protected function cacheKey(): string;

    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    abstract protected function modelClass(): string;

    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadAll();

        return $this->cache[$this->cacheKey()][$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->modelClass()::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        $cacheKey = $this->cacheKey();

        if (isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey][$key] = $value;
        }
    }

    /** @param array<string, mixed> $settings */
    public function setMany(array $settings): void
    {
        DB::transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
        });
    }

    public function loadAll(): void
    {
        $cacheKey = $this->cacheKey();

        if (isset($this->cache[$cacheKey])) {
            return;
        }

        $this->cache[$cacheKey] = $this->modelClass()::query()->pluck('value', 'key')->all();
    }

    public function flushCache(): void
    {
        $this->cache = [];
    }
}
