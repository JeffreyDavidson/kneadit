<?php

namespace App\Models;

use App\Services\PlatformSettingsManager;
use Database\Factories\PlatformSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\PlatformSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting query()
 *
 * @mixin \Eloquent
 */
class PlatformSetting extends Model
{
    /** @use HasFactory<PlatformSettingFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * @deprecated Use PlatformSettingsManager service via DI instead.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return resolve(PlatformSettingsManager::class)->get($key, $default);
    }

    /**
     * @deprecated Use PlatformSettingsManager service via DI instead.
     */
    public static function set(string $key, mixed $value): void
    {
        resolve(PlatformSettingsManager::class)->set($key, $value);
    }

    /**
     * @deprecated Use PlatformSettingsManager service via DI instead.
     */
    public static function flushCache(): void
    {
        resolve(PlatformSettingsManager::class)->flushCache();
    }
}
