<?php

namespace App\Models;

use App\Services\SettingsManager;
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

    /**
     * @deprecated Use SettingsManager service via DI instead.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return resolve(SettingsManager::class)->get($key, $default);
    }

    /**
     * @deprecated Use SettingsManager service via DI instead.
     */
    public static function set(string $key, mixed $value): void
    {
        resolve(SettingsManager::class)->set($key, $value);
    }

    /**
     * @deprecated Use SettingsManager service via DI instead.
     */
    public static function flushCache(): void
    {
        resolve(SettingsManager::class)->flushCache();
    }

    /**
     * @deprecated Use SettingsManager service via DI instead.
     */
    public static function pageContent(string $page, string $key, mixed $default = ''): mixed
    {
        return resolve(SettingsManager::class)->pageContent($page, $key, $default);
    }

    /**
     * @deprecated Use SettingsManager service via DI instead.
     *
     * @return array<string, mixed>
     */
    public static function pageContentAll(string $page): array
    {
        return resolve(SettingsManager::class)->pageContentAll($page);
    }
}
