<?php

namespace App\Filament\Widgets\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Contracts\Tenant;

trait CachesWidgetData
{
    /**
     * @template TValue
     *
     * @param array{int, int} $ttl [fresh_seconds, stale_seconds]
     * @param Closure(): TValue $resolver
     * @return TValue
     */
    protected function cached(string $segment, array $ttl, Closure $resolver): mixed
    {
        return Cache::flexible($this->widgetCacheKey($segment), $ttl, $resolver);
    }

    protected function widgetCacheKey(string $segment): string
    {
        $tenant = tenancy()->tenant;
        $tenantKey = $tenant instanceof Tenant ? $tenant->getTenantKey() : null;

        return $this->cachePrefix() . "_{$segment}_" . (is_scalar($tenantKey) ? (string) $tenantKey : 'none');
    }

    abstract protected function cachePrefix(): string;
}
