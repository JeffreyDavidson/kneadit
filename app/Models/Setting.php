<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'key',
        'value',
    ];

    protected static ?array $cache = null;

    public static function get(string $key, $default = null)
    {
        static::loadAll();

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Update in-memory cache
        if (static::$cache !== null) {
            static::$cache[$key] = $value;
        }
    }

    /**
     * Load all settings into memory with a single query.
     * Called once per request, then serves from memory.
     */
    public static function loadAll(): void
    {
        if (static::$cache !== null) {
            return;
        }

        static::$cache = static::pluck('value', 'key')->all();
    }

    /**
     * Get page content for a specific page and key.
     * Usage: Setting::pageContent('menu', 'hero_title', 'Our Menu')
     */
    public static function pageContent(string $page, string $key, $default = '')
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
     * Clear the in-memory cache (useful for testing or after bulk updates).
     */
    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
