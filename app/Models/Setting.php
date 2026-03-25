<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Database\Factories\SettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 *
 * @mixin \Eloquent
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'key',
        'value',
    ];

    protected static array $cache = [];

    /**
     * Get the cache key for the current tenant context.
     */
    protected static function tenantCacheKey(): string
    {
        return tenant() ? tenant()->getTenantKey() : 'central';
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        static::loadAll();

        $tenantKey = static::tenantCacheKey();

        return static::$cache[$tenantKey][$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        // Update in-memory cache
        $tenantKey = static::tenantCacheKey();

        if (isset(static::$cache[$tenantKey])) {
            static::$cache[$tenantKey][$key] = $value;
        }
    }

    /**
     * Load all settings into memory with a single query.
     * Called once per tenant per request, then serves from memory.
     */
    public static function loadAll(): void
    {
        $tenantKey = static::tenantCacheKey();

        if (isset(static::$cache[$tenantKey])) {
            return;
        }

        static::$cache[$tenantKey] = static::query()->pluck('value', 'key')->all();
    }

    /**
     * Get page content for a specific page and key.
     * Usage: Setting::pageContent('menu', 'hero_title', 'Our Menu')
     */
    public static function pageContent(string $page, string $key, mixed $default = ''): mixed
    {
        $content = json_decode(static::get('page_content', '{}'), true);

        return $content[$page][$key] ?? $default;
    }

    /**
     * Get all page content for a specific page.
     * Usage: Setting::pageContentAll('menu')
     */
    public static function pageContentAll(string $page): array
    {
        $content = json_decode(static::get('page_content', '{}'), true);

        return $content[$page] ?? [];
    }

    /**
     * Clear the in-memory cache for all tenants (useful for testing or after bulk updates).
     */
    public static function flushCache(): void
    {
        static::$cache = [];
    }
}
