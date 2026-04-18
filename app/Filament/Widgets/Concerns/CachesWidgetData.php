<?php

namespace App\Filament\Widgets\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

trait CachesWidgetData
{
    /**
     * @param array{int, int} $ttl [fresh_seconds, stale_seconds]
     */
    protected function cached(string $segment, array $ttl, Closure $resolver): mixed
    {
        return Cache::flexible($this->widgetCacheKey($segment), $ttl, $resolver);
    }

    protected function widgetCacheKey(string $segment): string
    {
        return $this->cachePrefix() . "_{$segment}_" . (tenant()?->getTenantKey() ?? 'none');
    }

    abstract protected function cachePrefix(): string;
}
