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
        $storedValue = $this->valueForStorage($key, $value);

        $this->modelClass()::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue],
        );

        $cacheKey = $this->cacheKey();

        if (isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey][$key] = $this->valueFromStorage($key, $storedValue);
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

        $stored = $this->modelClass()::query()->pluck('value', 'key')->all();
        $settings = [];

        foreach ($stored as $key => $value) {
            $settings[$key] = $this->valueFromStorage($key, $value);
        }

        $this->cache[$cacheKey] = $settings;
    }

    public function flushCache(): void
    {
        $this->cache = [];
    }

    protected function valueForStorage(string $key, mixed $value): mixed
    {
        return $value;
    }

    protected function valueFromStorage(string $key, mixed $value): mixed
    {
        return $value;
    }
}
