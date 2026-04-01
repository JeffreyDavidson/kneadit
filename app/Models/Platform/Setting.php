<?php

namespace App\Models\Platform;

use App\Models\Concerns\LogsActivity;
use Database\Factories\Platform\SettingFactory;
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

    protected static function newFactory(): SettingFactory
    {
        return SettingFactory::new();
    }
}
