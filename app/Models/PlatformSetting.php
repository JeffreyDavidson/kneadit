<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'key',
        'value',
    ];

    protected static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        static::loadAll();

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        if (static::$cache !== null) {
            static::$cache[$key] = $value;
        }
    }

    public static function loadAll(): void
    {
        if (static::$cache !== null) {
            return;
        }

        static::$cache = static::pluck('value', 'key')->all();
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
