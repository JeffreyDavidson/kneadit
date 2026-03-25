<?php

namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereValue($value)
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

    protected static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        static::loadAll();

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        if (static::$cache !== null) {
            static::$cache[$key] = $value;
        }
    }

    public static function loadAll(): void
    {
        if (static::$cache !== null) {
            return;
        }

        static::$cache = static::query()->pluck('value', 'key')->all();
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
